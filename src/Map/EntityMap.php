<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use ArrayAccess;
use WebChemistry\DoctrineExtras\Map\Exception\OutOfBoundsException;

/**
 * @template TEntity of object
 * @template TValue
 * @extends ArrayAccess<TEntity|int|string|array<string, TEntity|int|string|null>|null, TValue>
 */
interface EntityMap extends ArrayAccess
{

	/**
	 * @param TEntity|int|string|array<string, TEntity|int|string|null>|null $id
	 * @return TValue
	 * @throws OutOfBoundsException
	 */
	public function get(object|int|string|array|null $id): mixed;

	/**
	 * @param TEntity|int|string|array<string, TEntity|int|string|null>|null $id
	 * @param TValue $value
	 * @return TValue
	 */
	public function getOr(object|int|string|array|null $id, mixed $value): mixed;

	/**
	 * @param TEntity|int|string|array<string, TEntity|int|string|null>|null $id
	 * @return TValue|null
	 */
	public function getNullable(object|int|string|array|null $id): mixed;

	/**
	 * @param string $column
	 * @return mixed[]
	 */
	public function column(string $column): array;

	/**
	 * @return TValue[]
	 */
	public function getMap(): array;

	/**
	 * @param TEntity|int|string|array<string, TEntity|int|string|null>|null $offset
	 */
	public function offsetExists(mixed $offset): bool;

	/**
	 * @param TEntity|int|string|array<string, TEntity|int|string|null>|null $offset
	 * @return TValue
	 * @throws OutOfBoundsException
	 */
	public function offsetGet(mixed $offset): mixed;

}
