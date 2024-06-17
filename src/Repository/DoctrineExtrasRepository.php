<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use LogicException;
use Nette\NotSupportedException;
use Nette\Utils\Arrays;
use Stringable;
use WebChemistry\DoctrineExtras\Identity\EntityWithIdentity;
use WebChemistry\DoctrineExtras\Index\EntityIndexFactory;
use WebChemistry\DoctrineExtras\Map\ArrayEntityMapBuilder;
use WebChemistry\DoctrineExtras\Map\ArrayExistentialMap;
use WebChemistry\DoctrineExtras\Map\CountMap;
use WebChemistry\DoctrineExtras\Map\EmptyEntityMap;
use WebChemistry\DoctrineExtras\Map\EmptyExistentialMap;
use WebChemistry\DoctrineExtras\Map\EntityMap;
use WebChemistry\DoctrineExtras\Map\ExistentialMap;
use WebChemistry\DoctrineExtras\Map\ObjectEntityMapBuilder;

final class DoctrineExtrasRepository
{

	public const Joins = 'joins';

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	/**
	 * @template TEntity of object
	 * @template TValue
	 * @param class-string<TEntity> $className
	 * @param TValue[] $results
	 * @return EntityMap<TEntity, TValue>
	 */
	public function createMapByResult(string $className, array $results, string $idField, bool $strict = false): EntityMap
	{
		if (!$results) {
			/** @var EntityMap<TEntity, TValue> */
			return new EmptyEntityMap($this->em);
		}

		$metadata = $this->em->getClassMetadata($className);
		/** @var ArrayEntityMapBuilder<TEntity, TValue> $builder */
		$builder = new ArrayEntityMapBuilder($metadata);

		foreach ($results as $i => $result) {
			$idValue = $result[$idField] ?? null;

			if ($idValue === null) {
				if (!$strict) {
					continue;
				}

				throw new LogicException(sprintf('Result "%s" does not contain field %s.', $i, $idField));
			}

			$builder->add($idValue, $result); // @phpstan-ignore-line
		}

		return $builder->build($this->em);
	}

	/**
	 * @template TEntity of object
	 * @param mixed[] $ids
	 * @param class-string<TEntity> $className
	 * @param mixed[] $options
	 * @return EntityMap<TEntity, TEntity>
	 */
	public function createSelfMap(array $ids, string $className, array $options = []): EntityMap
	{
		$metadata = $this->em->getClassMetadata($className);

		if ($metadata->isIdentifierComposite) {
			throw new NotSupportedException(
				sprintf('Entity %s has composite identifier, which is not supported.', $className)
			);
		}

		if (!isset($options[self::Joins])) {
			$repository = $this->em->getRepository($className);
			/** @var TEntity[] $entities */
			$entities = $repository->findBy([
				$metadata->getSingleIdentifierFieldName() => $ids,
			]);
		} else {
			/** @var string[] $joins */
			$joins = $options[self::Joins];

			$qb = $this->em->createQueryBuilder()
				->select('e')
				->from($className, 'e');

			foreach ($joins as $i => $field) {
				$qb->addSelect(sprintf('j%s', $i));

				$qb->leftJoin(sprintf('e.%s', $field), sprintf('j%s', $i));
			}

			$qb->where(sprintf('e.%s IN (:ids)', $metadata->getSingleIdentifierFieldName()))
				->setParameter('ids', $ids);

			/** @var TEntity[] $entities */
			$entities = $qb->getQuery()->getResult();
		}

		/** @var ObjectEntityMapBuilder<TEntity, TEntity> $builder */
		$builder = new ObjectEntityMapBuilder($metadata);

		foreach ($entities as $entity) {
			$builder->add($entity, $entity);
		}

		return $builder->build($this->em);
	}

	/**
	 * @template TEntity of object
	 * @template TAssoc of object
	 * @param TEntity[] $sources
	 * @param class-string<TAssoc> $target
	 * @return ExistentialMap<TEntity>
	 */
	public function createExistentialMap(array $sources, string $target, ?Criteria $criteria = null): ExistentialMap
	{
		$first = $this->getFirst($sources);

		if (!$first) {
			/** @var ArrayExistentialMap<TEntity> */
			return new EmptyExistentialMap($this->em);
		}

		$sourceClass = ClassUtils::getClass($first);
		$metadata = $this->em->getClassMetadata($target);
		$field = $this->getFirstField($metadata->getAssociationsByTargetClass($sourceClass), $target, $sourceClass);

		$qb = $this->em->createQueryBuilder()
			->select(sprintf('IDENTITY(e.%s)', $field))
			->groupBy(sprintf('e.%s', $field))
			->from($target, 'e')
			->where(sprintf('e.%s IN (:sources)', $field))
			->setParameter('sources', $sources);

		if ($criteria) {
			$qb->addCriteria($criteria);
		}

		/** @var array{1: int}[] $associations */
		$associations = $qb->getQuery()
			->getResult();

		$entries = [];
		$index = (new EntityIndexFactory($this->em))->create($sources);

		foreach ($associations as $association) {
			$sourceId = $association[1];

			$entity = $index->find($sourceId);

			if ($entity) {
				$entries[] = [$entity, true];
			}
		}

		/** @var ExistentialMap<TEntity> */
		return new ArrayExistentialMap($entries, $this->em->getClassMetadata($sourceClass), $this->em);
	}

	/**
	 * @template TEntity of object
	 * @template TAssoc of object
	 * @param TEntity[] $sources
	 * @param class-string<TAssoc> $target
	 * @return CountMap<TEntity>
	 */
	public function createCountMap(array $sources, string $target, ?Criteria $criteria = null): CountMap
	{
		$first = $this->getFirst($sources);

		if (!$first) {
			/** @var CountMap<TEntity> */
			return new CountMap([]);
		}

		$sourceClass = ClassUtils::getClass($first);
		$metadata = $this->em->getClassMetadata($target);
		$field = $this->getFirstField($metadata->getAssociationsByTargetClass($sourceClass), $target, $sourceClass);

		$qb = $this->em->createQueryBuilder()
			->select(sprintf('COUNT(e.%s), IDENTITY(e.%s)', $field, $field))
			->groupBy(sprintf('e.%s', $field))
			->from($target, 'e')
			->where(sprintf('e.%s IN (:sources)', $field))
			->setParameter('sources', $sources);

		if ($criteria) {
			$qb->addCriteria($criteria);
		}

		/** @var array{1: int, 2: string|int}[] $associations */
		$associations = $qb->getQuery()
			->getResult();

		$entries = [];

		$index = (new EntityIndexFactory($this->em))->create($sources);

		foreach ($associations as $association) {
			$count = $association[1];
			$sourceId = $association[2];

			$entity = $index->find($sourceId);

			if ($entity) {
				$entries[] = [$entity, $count];
			}
		}

		/** @var CountMap<TEntity> */
		return new CountMap($entries);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @param array<string|int> $ids
	 * @param 'DESC'|'ASC'|null $sort
	 * @param array<string, string> $joins join => alias
	 * @return T[]
	 */
	public function findMany(string $className, array $ids, ?string $sort = null, array $joins = [], bool $sortByGiven = false): array
	{
		$metadata = $this->em->getClassMetadata($className);

		$fields = $metadata->getIdentifierFieldNames();

		if (count($metadata->getIdentifier()) > 1) {
			throw new NotSupportedException(
				sprintf('Entity %s has more than one identifier, which is not supported yet.', $className)
			);
		}

		$field = Arrays::first($fields);

		if (!$field) {
			throw new LogicException(sprintf('Entity %s does not have any identifier.', $className));
		}

		if (!$ids) {
			return [];
		}

		if (!$joins && !$sort) {
			$values = $this->em->getRepository($className)->findBy([
				$field => $ids,
			]);

			if ($sortByGiven) {
				$sorted = [];
				$outOfRange = [];

				foreach ($values as $value) {
					$position = array_search($metadata->getFieldValue($value, $field), $ids, true);

					if ($position === false) {
						$outOfRange[] = $value;
					} else {
						$sorted[$position] = $value;
					}
				}


				$values = [...$sorted, ...$outOfRange];
			}

			return $values;
		}

		$qb = $this->em->createQueryBuilder()
			->select('e')
			->from($className, 'e')
			->where(sprintf('e.%s IN(:ids)', $field))
			->setParameter('ids', $ids);

		foreach ($joins as $join => $alias) {
			$qb->leftJoin($join, $alias)
				->addSelect($alias);
		}

		if ($sortByGiven) {
			$qb->orderBy(sprintf('FIELD(e.%s, :ids)', $field));
		}

		if ($sort) {
			$qb->orderBy(sprintf('FIELD(e.%s, :ids)', $field), $sort);
		}

		/** @var T[] */
		return $qb->getQuery()->getResult();
	}

	/**
	 * @template TEntity of object
	 * @template TAssoc of object
	 * @param TEntity[] $sources
	 * @param class-string<TAssoc> $target
	 * @return EntityMap<TEntity, TAssoc>
	 */
	public function createOneToOneMap(array $sources, string $target, ?Criteria $criteria = null): EntityMap
	{
		$first = $this->getFirst($sources);

		if (!$first) {
			/** @var EntityMap<TEntity, TAssoc> */
			return new EmptyEntityMap($this->em);
		}

		$sourceClass = ClassUtils::getClass($first);
		$metadata = $this->em->getClassMetadata($target);
		$field = $this->getFirstField($metadata->getAssociationsByTargetClass($sourceClass), $target, $sourceClass);

		if (!$criteria) {
			/** @var TAssoc[] $associations */
			$associations = $this->em->getRepository($target)->findBy([
				$field => $sources,
			]);
		} else {
			$qb = $this->em->createQueryBuilder()
				->select('e')
				->from($target, 'e')
				->where(sprintf('e.%s IN (:sources)', $field))
				->setParameter('sources', $sources)
				->addCriteria($criteria);

			/** @var TAssoc[] $associations */
			$associations = $qb->getQuery()
				->getResult();
		}

		/** @var ObjectEntityMapBuilder<TEntity, TAssoc> $builder */
		$builder = new ObjectEntityMapBuilder($this->em->getClassMetadata($sourceClass));

		foreach ($associations as $association) {
			/** @var TEntity $mainEntity */
			$mainEntity = $metadata->getFieldValue($association, $field);

			$builder->add($mainEntity, $association);
		}

		return $builder->build($this->em);
	}

	/**
	 * @template TEntity of object
	 * @template TAssoc of object
	 * @param class-string<TEntity> $primary
	 * @param TAssoc[] $associations
	 * @return EntityMap<TEntity, TAssoc>
	 */
	public function createMap(string $primary, array $associations): EntityMap
	{
		$firstAssoc = $this->getFirst($associations);

		if (!$firstAssoc) {
			/** @var EntityMap<TEntity, TAssoc> */
			return new EmptyEntityMap($this->em);
		}

		$assocClass = ClassUtils::getClass($firstAssoc);

		$metadata = $this->em->getClassMetadata($assocClass);
		$field = $this->getFirstField($metadata->getAssociationsByTargetClass($primary), $assocClass, $primary);

		/** @var ObjectEntityMapBuilder<TEntity, TAssoc> $builder */
		$builder = new ObjectEntityMapBuilder($this->em->getClassMetadata($primary));

		foreach ($associations as $association) {
			/** @var TEntity $mainEntity */
			$mainEntity = $metadata->getFieldValue($association, $field);

			$builder->add($mainEntity, $association);
		}

		return $builder->build($this->em);
	}

	/**
	 * @param array<string, mixed[]> $metadata
	 * @param class-string $sourceClassName
	 * @param class-string $targetClassName
	 * @return string
	 */
	private function getFirstField(array $metadata, string $sourceClassName, string $targetClassName): string
	{
		$count = count($metadata);

		if ($count === 0) {
			throw new LogicException(sprintf(
				'Entity %s does not have any association to %s.',
				$sourceClassName,
				$targetClassName,
			));
		}

		if ($count > 1) {
			throw new LogicException(sprintf(
				'Entity %s has more than one association to %s.',
				$sourceClassName,
				$targetClassName,
			));
		}

		return array_key_first($metadata);
	}

	/**
	 * @template T
	 * @param T[] $values
	 * @return T|null
	 */
	private function getFirst(array $values): mixed
	{
		$key = array_key_first($values);

		if ($key === null) {
			return null;
		}

		return $values[$key];
	}

}
