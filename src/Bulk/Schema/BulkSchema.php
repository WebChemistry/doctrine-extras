<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Schema;

use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;

/**
 * @template TEntity of object
 */
final class BulkSchema
{

	/**
	 * @param class-string<TEntity> $className
	 * @param string[] $fields
	 * @param array<string, mixed> $defaults
	 */
	public function __construct(
		private string $className,
		private array $fields,
		private array $defaults = [],
	)
	{
	}

	/**
	 * @return BulkBlueprint<TEntity>
	 */
	public function createBlueprint(EntityManagerInterface $em): BulkBlueprint
	{
		/** @var BulkBlueprint<TEntity> */
		return new BulkBlueprint($this->className, $em, $this->fields, $this->defaults);
	}

}
