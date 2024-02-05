<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use ArrayAccess;
use InvalidArgumentException;
use LogicException;
use WeakMap;

/**
 * @template TEntityClass of object
 * @implements ArrayAccess<TEntityClass, int>
 */
final class CountMap implements ArrayAccess
{

	/** @var WeakMap<TEntityClass, int> */
	private WeakMap $map;

	/**
	 * @param array{TEntityClass, int}[] $entries
	 */
	public function __construct(array $entries)
	{
		$this->map = new WeakMap();

		foreach ($entries as [$entity, $count]) {
			$this->map[$entity] = $count;
		}
	}

	/**
	 * @param TEntityClass $offset
	 */
	public function offsetExists(mixed $offset): bool
	{
		if (!is_object($offset)) {
			throw new InvalidArgumentException(sprintf('Offset must be an object, %s given.', gettype($offset)));
		}

		return isset($this->map[$offset]);
	}

	/**
	 * @param TEntityClass $offset
	 */
	public function offsetGet(mixed $offset): int
	{
		if (!is_object($offset)) {
			throw new InvalidArgumentException(sprintf('Offset must be an object, %s given.', gettype($offset)));
		}

		return $this->map[$offset] ?? 0;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new LogicException('Not supported.');
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new LogicException('Not supported.');
	}

}
