<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Index;

use Doctrine\ORM\EntityManagerInterface;
use OutOfBoundsException;

/**
 * @template T of object
 */
final class EntityIndex
{

	/**
	 * @param array<string|int, T> $index
	 */
	public function __construct(
		private array $index = [],
	)
	{
	}

	public function has(string|int $id): bool
	{
		return isset($this->index[$id]);
	}

	/**
	 * @return T
	 */
	public function get(string|int $id): object
	{
		return $this->index[$id] ?? throw new OutOfBoundsException(sprintf('Entity with id %s does not exist.', $id));
	}

	/**
	 * @return T|null
	 */
	public function find(string|int $id): ?object
	{
		return $this->index[$id] ?? null;
	}

}
