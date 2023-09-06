<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Identity;

use InvalidArgumentException;

final class EntityIdentity
{

	private string $uniqueId;

	/** @var array<string|int> */
	private array $ids;

	private bool $single;

	/**
	 * @param class-string<EntityWithIdentity> $className
	 */
	public function __construct(
		private string $className,
		string|int ...$ids,
	)
	{
		$this->ids = $ids;
		$this->single = count($ids) === 1;
	}

	/**
	 * @return string|int
	 */
	public function getId(): int|string
	{
		if (!$this->single) {
			throw new InvalidArgumentException('Identity has more than one id.');
		}

		return $this->ids[0];
	}

	/**
	 * @return array<string|int>
	 */
	public function getIds(): array
	{
		return $this->ids;
	}

	public function getUniqueId(): string
	{
		return $this->uniqueId ??= sprintf('%s(%s)', $this->className, implode(',', array_map(
			fn (string|int $value): string => (string) $value,
			$this->ids,
		)));
	}

	/**
	 * @return class-string<EntityWithIdentity>
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

	/**
	 * @param class-string<EntityWithIdentity> $className
	 * @param EntityWithIdentity|EntityIdentity|string|int $entity
	 * @return EntityIdentity
	 */
	public static function create(string $className, EntityWithIdentity|self|string|int $entity, string|int ...$ids): self
	{
		if (is_object($entity)) {
			if ($entity instanceof self) {
				if ($entity->className !== $className) {
					throw new InvalidArgumentException(
						sprintf('Given identity is of class %s, %s expected.', $entity->className, $className)
					);
				}

				return $entity;
			}

			if (!$entity instanceof $className) {
				throw new InvalidArgumentException(
					sprintf(
						'Given object of %s is not instance of %s.',
						get_debug_type($entity),
						$className,
					)
				);
			}

			return $entity->identity();
		}

		return new self($className, $entity, ...$ids);
	}

	public function __toString(): string
	{
		return $this->getUniqueId();
	}

}
