<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @template TEntity of object
 * @template TValue
 */
final class ArrayEntityMapBuilder
{

	/** @var array{string|int|float|array<string|int|float>, TValue}[] */
	private array $entries = [];

	/**
	 * @param ClassMetadata<TEntity> $classMetadata
	 */
	public function __construct(
		private ClassMetadata $classMetadata,
	)
	{
	}

	/**
	 * @param string|int|float|array<string|int|float> $id
	 * @param TValue $value
	 * @return self<TEntity, TValue>
	 */
	public function add(string|int|float|array $id, mixed $value): self
	{
		$this->entries[] = [$id, $value];

		return $this;
	}

	/**
	 * @return EntityMap<TEntity, TValue>
	 */
	public function build(?EntityManagerInterface $em = null): EntityMap
	{
		return new ArrayEntityMap($this->entries, $this->classMetadata, $em);
	}

}
