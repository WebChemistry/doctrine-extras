<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use Doctrine\Common\Util\ClassUtils;
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

	public function has(object|int|array|string|null $id): bool
	{
		$key = $this->getKeyForId($id);

		if ($key === null) {
			return false;
		}

		return array_key_exists($key, $this->map);
	}

	public function getNullable(object|int|array|string|null $id): mixed
	{
		$key = $this->getKeyForId($id);

		if ($key === null) {
			return null;
		}

		return $this->map[$key] ?? null;
	}

	public function column(string $column): array
	{
		$values = [];

		foreach ($this->map as $value) {
			if (is_array($value)) {
				$values[] = $value[$column];
			} else if (is_object($value)) {
				if ($em = $this->em) {
					$metadata = $em->getClassMetadata(ClassUtils::getClass($value));

					$values[] = $metadata->getFieldValue($value, $column);
				} else {
					throw new NotSupportedException('Entity manager is not set.');
				}
			}
		}

		return $values;
	}

	public function getMap(): array
	{
		return $this->map;
	}

	final public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	final public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	/**
	 * @throws NotSupportedException
	 */
	final public function offsetSet(mixed $offset, mixed $value): never
	{
		throw NotSupportedException::operation(__METHOD__);
	}

	/**
	 * @throws NotSupportedException
	 */
	final public function offsetUnset(mixed $offset): never
	{
		throw NotSupportedException::operation(__METHOD__);
	}

	/**
	 * @param TEntity|int|string|array<string, TEntity|int|string|null>|null $id
	 */
	protected function getKeyForId(object|int|array|string|null $id): ?string
	{
		return EntityMapHelper::stringifyIdByClassMetadata($this->classMetadata, $id);
	}

}
