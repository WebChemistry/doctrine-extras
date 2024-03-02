<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use WebChemistry\DoctrineExtras\Map\Exception\NotSupportedException;
use WebChemistry\DoctrineExtras\Map\Exception\OutOfBoundsException;

/**
 * @template TEntity of object
 * @template TValue
 * @implements EntityMap<TEntity, TValue>
 */
abstract class BaseEntityMap implements EntityMap
{

	/** @var array<string, TValue> */
	protected array $map = [];

	/**
	 * @param ClassMetadata<TEntity> $classMetadata
	 */
	public function __construct(
		protected ClassMetadata $classMetadata,
		protected ?EntityManagerInterface $em = null,
	)
	{
	}

	public function get(object|int|array|string|null $id): mixed
	{
		$key = $this->getKeyForId($id);

		if ($key === null || !array_key_exists($key, $this->map)) {
			OutOfBoundsException::notExists($id, $this->em);
		}

		return $this->map[$key];
	}

	public function getOr(object|int|array|string|null $id, mixed $value): mixed
	{
		$key = $this->getKeyForId($id);

		if ($key === null) {
			return $value;
		}

		return $this->map[$key] ?? $value;
	}

	public function getNullable(object|int|array|string|null $id): mixed
	{
		$key = $this->getKeyForId($id);

		if ($key === null) {
			return null;
		}

		return $this->map[$key] ?? null;
	}

	public function offsetExists(mixed $offset): bool
	{
		$key = $this->getKeyForId($offset);

		if ($key === null) {
			return false;
		}

		return array_key_exists($key, $this->map);
	}

	public function offsetGet(mixed $offset): mixed
	{
		$key = $this->getKeyForId($offset);

		if ($key === null || !array_key_exists($key, $this->map)) {
			OutOfBoundsException::notExists($offset, $this->em);
		}

		return $this->map[$key];
	}

	/**
	 * @throws NotSupportedException
	 */
	public function offsetSet(mixed $offset, mixed $value): never
	{
		throw new NotSupportedException(__METHOD__);
	}

	/**
	 * @throws NotSupportedException
	 */
	public function offsetUnset(mixed $offset): never
	{
		throw new NotSupportedException(__METHOD__);
	}

	/**
	 * @param TEntity|int|string|array<string, TEntity|int|string|null>|null $id
	 */
	protected function getKeyForId(object|int|array|string|null $id): ?string
	{
		return EntityMapHelper::stringifyIdByClassMetadata($this->classMetadata, $id);
	}

}
