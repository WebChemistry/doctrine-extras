<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use ArrayAccess;
use Nette\NotSupportedException;
use OutOfBoundsException;
use TypeError;
use WebChemistry\DoctrineExtras\Identity\EntityIdentity;
use WebChemistry\DoctrineExtras\Identity\EntityWithIdentity;

/**
 * @template TEntity of EntityWithIdentity
 * @template TValue
 * @implements ArrayAccess<TEntity|EntityIdentity|string|int|array<string|int>, TValue>
 */
final class EntityMap implements ArrayAccess
{

	/**
	 * @param array<string, TValue> $map
	 */
	protected function __construct(
		private array $map,
	)
	{
	}

	public static function empty(): static
	{
		return new EntityMap([]); // @phpstan-ignore-line
	}

	/**
	 * @param array{TEntity, TValue}[] $entries
	 * @return static<TEntity, TValue>
	 */
	public static function fromEntries(array $entries): static
	{
		$map = [];

		foreach ($entries as [$entity, $value]) {
			$map[$entity->identity()->getUniqueId()] = $value;
		}

		return new static($map);
	}

	/**
	 * @param TEntity|EntityIdentity $entityOrIdentity
	 * @return TValue
	 */
	public function get(EntityWithIdentity|EntityIdentity $entityOrIdentity): mixed
	{
		$identity = $this->getIdentity($entityOrIdentity);

		return $this->map[$identity->getUniqueId()] ?? throw new OutOfBoundsException(
			sprintf('Identity %s does not exist in map.', $identity->getUniqueId()),
		);
	}

	/**
	 * @param TEntity|EntityIdentity $entityOrIdentity
	 * @param TValue $value
	 * @return TValue
	 */
	public function getOr(EntityWithIdentity|EntityIdentity $entityOrIdentity, mixed $value): mixed
	{
		$identity = $this->getIdentity($entityOrIdentity);

		return $this->map[$identity->getUniqueId()] ?? $value;
	}

	/**
	 * @param TEntity|EntityIdentity $entityOrIdentity
	 * @return TValue|null
	 */
	public function getNullable(EntityWithIdentity|EntityIdentity $entityOrIdentity): mixed
	{
		$identity = $this->getIdentity($entityOrIdentity);

		return $this->map[$identity->getUniqueId()] ?? null;
	}

	/**
	 * @param TEntity $offset
	 */
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->map[$this->getIdentity(
			$this->checkOffset($offset),
		)->getUniqueId()]);
	}

	/**
	 * @param TEntity|EntityIdentity $offset
	 * @return TValue
	 */
	public function offsetGet(mixed $offset): mixed
	{
		$identity = $this->getIdentity($this->checkOffset($offset));

		return $this->map[$identity->getUniqueId()] ?? throw new OutOfBoundsException(
			sprintf('Identity %s does not exist in map.', $identity->getUniqueId()),
		);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new NotSupportedException();
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new NotSupportedException();
	}

	/**
	 * @return TEntity|EntityIdentity
	 */
	private function checkOffset(mixed $offset): EntityWithIdentity|EntityIdentity
	{
		if (!$offset instanceof EntityWithIdentity && !$offset instanceof EntityIdentity) {
			throw new TypeError(
				sprintf(
					'Offset argument must be of the type %s|%s, %s given.',
					EntityWithIdentity::class,
					EntityIdentity::class,
					get_debug_type($offset)
				)
			);
		}

		/** @var TEntity|EntityIdentity */
		return $offset;
	}

	/**
	 * @param TEntity|EntityIdentity $entityOrIdentity
	 * @return EntityIdentity
	 */
	private function getIdentity(EntityWithIdentity|EntityIdentity $entityOrIdentity): EntityIdentity
	{
		return $entityOrIdentity instanceof EntityWithIdentity ? $entityOrIdentity->identity() : $entityOrIdentity;
	}

}
