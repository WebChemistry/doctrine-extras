<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @template TEntity of object
 * @template TValue
 */
final class ObjectEntityMapBuilder
{

	/** @var array{TEntity, TValue}[] */
	private array $entries = [];

	/**
	 * @param ClassMetadata<TEntity> $classMetadata
	 */
	public function __construct(
		private ClassMetadata $classMetadata,
	)
	{
	}

	/**
	 * @param TEntity $entity
	 * @param TValue $value
	 * @return self<TEntity, TValue>
	 */
	public function add(object $entity, mixed $value): self
	{
		$this->entries[] = [$entity, $value];

		return $this;
	}

	/**
	 * @return EntityMap<TEntity, TValue>
	 */
	public function build(?EntityManagerInterface $em = null): EntityMap
	{
		return new ObjectEntityMap($this->entries, $this->classMetadata, $em);
	}

}
