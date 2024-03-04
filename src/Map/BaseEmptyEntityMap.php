<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Map\Exception\NotSupportedException;
use WebChemistry\DoctrineExtras\Map\Exception\OutOfBoundsException;

/**
 * @template TEntity of object
 * @template TValue
 * @implements EntityMap<TEntity, TValue>
 */
abstract class BaseEmptyEntityMap implements EntityMap
{

	public function __construct(
		private ?EntityManagerInterface $em = null,
	)
	{
	}

	public function get(object|int|array|string|null $id): mixed
	{
		throw OutOfBoundsException::notExists($id, $this->em);
	}

	public function getOr(object|int|array|string|null $id, mixed $value): mixed
	{
		return $value;
	}

	public function has(object|int|array|string|null $id): bool
	{
		return false;
	}

	public function getNullable(object|int|array|string|null $id): mixed
	{
		return null;
	}

	public function column(string $column): array
	{
		return [];
	}

	public function getMap(): array
	{
		return [];
	}

	final public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	final public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	final public function offsetSet(mixed $offset, mixed $value): never
	{
		throw NotSupportedException::operation(__METHOD__);
	}

	final public function offsetUnset(mixed $offset): never
	{
		throw NotSupportedException::operation(__METHOD__);
	}

}
