<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Bulk\Dialect\Dialect;
use WebChemistry\DoctrineExtras\Bulk\Hook\BulkHook;
use WebChemistry\DoctrineExtras\Bulk\Schema\BulkSchema;

final class BulkBuilderFactory
{

	/**
	 * @param BulkHook[] $hooks
	 */
	public function __construct(
		private EntityManagerInterface $em,
		private Dialect $dialect,
		private array $hooks = [],
	)
	{
	}

	/**
	 * @template TEntity of object
	 * @param BulkSchema<TEntity> $schema
	 * @return BulkBuilder<TEntity>
	 */
	public function create(BulkSchema $schema): BulkBuilder
	{
		return new BulkBuilder($this->em, $this->dialect, $schema->createBlueprint($this->em), $this->hooks);
	}

}
