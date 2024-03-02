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
final class ArrayEntityMap extends BaseEntityMap
{

	/**
	 * @param array{string|int|float|array<string|int|float>, TValue}[] $entries
	 * @param ClassMetadata<TEntity> $classMetadata
	 */
	public function __construct(
		array $entries,
		ClassMetadata $classMetadata,
		?EntityManagerInterface $em = null,
	)
	{
		parent::__construct($classMetadata, $em);

		foreach ($entries as [$id, $value]) {
			if (is_array($id)) {
				$id = serialize($id);
			} else {
				$id = (string) $id;
			}

			$this->map[$id] = $value;
		}
	}

}
