<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;
use WebChemistry\DoctrineExtras\Utility\DoctrineUtility;

/**
 * @template TEntity of object
 * @template TValue
 * @extends BaseEntityMap<TEntity, TValue>
 */
final class ObjectEntityMap extends BaseEntityMap
{

	/**
	 * @param array{TEntity, TValue}[] $entries
	 * @param ClassMetadata<TEntity> $classMetadata
	 */
	public function __construct(
		array $entries,
		ClassMetadata $classMetadata,
		?EntityManagerInterface $em = null,
	)
	{
		parent::__construct($classMetadata, $em);

		foreach ($entries as [$entity, $value]) {
			if (!DoctrineUtility::isEntityEqualToMetadata($entity, $this->classMetadata)) {
				throw new InvalidArgumentException(sprintf(
					'Entity %s is not equal to metadata %s.',
					get_debug_type($entity),
					$this->classMetadata->getName(),
				));
			}

			$this->map[DoctrineUtility::getStringId($this->classMetadata, $entity)] = $value;
		}
	}

}
