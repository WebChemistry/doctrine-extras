<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use ArrayAccess;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;
use WebChemistry\DoctrineExtras\Map\Exception\OutOfBoundsException;
use WebChemistry\DoctrineExtras\Utility\DoctrineUtility;

/**
 * @template TEntity of object
 * @extends BaseEntityMap<TEntity, bool>
 * @implements ExistentialMap<TEntity>
 */
final class ArrayExistentialMap extends BaseEntityMap implements ExistentialMap
{

	/**
	 * @param array{TEntity, bool}[] $entries
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

	public function get(object|array|int|string|null $id): mixed
	{
		$key = $this->getKeyForId($id);

		if ($key === null) {
			OutOfBoundsException::notExists($id, $this->em);
		}

		return $this->map[$key] ?? false;
	}

}
