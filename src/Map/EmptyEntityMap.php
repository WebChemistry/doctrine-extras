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
final class EmptyEntityMap implements EntityMap
{

	public function __construct(
		private ?EntityManagerInterface $em = null,
	)
	{
	}

	public function get(object|int|array|string|null $id): never
	{
		throw OutOfBoundsException::notExists($id, $this->em);
	}

	public function getOr(object|int|array|string|null $id, mixed $value): mixed
	{
		return $value;
	}

	public function getNullable(object|int|array|string|null $id): mixed
	{
		return null;
	}

	public function offsetExists(mixed $offset): bool
	{
		return false;
	}

	public function offsetGet(mixed $offset): never
	{
		throw OutOfBoundsException::notExists($offset, $this->em);
	}

	public function offsetSet(mixed $offset, mixed $value): never
	{
		throw new NotSupportedException(__METHOD__);
	}

	public function offsetUnset(mixed $offset): never
	{
		throw new NotSupportedException(__METHOD__);
	}

}
