<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;
use WebChemistry\DoctrineExtras\Bulk\Dialect\Dialect;
use WebChemistry\DoctrineExtras\Bulk\Hook\BulkHook;
use WebChemistry\DoctrineExtras\Bulk\Message\BulkMessage;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;

/**
 * @template TEntity of object
 */
final class Bulk
{

	/**
	 * @param BulkBlueprint<TEntity> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 */
	public function __construct(
		private EntityManagerInterface $em,
		private BulkBlueprint $blueprint,
		private Dialect $dialect,
		private array $packets,
		private array $hooks = [],
	)
	{
	}

	public function insert(bool $skipDuplications = false): BulkMessage
	{
		return $this->dialect->insert($this->blueprint, $this->packets, $this->hooks, $skipDuplications);
	}

	public function executeInsert(bool $skipDuplications = false): int
	{
		return $this->insert($skipDuplications)->send($this->em);
	}

	public function upsert(): BulkMessage
	{
		return $this->dialect->upsert($this->blueprint, $this->packets, $this->hooks);
	}

	public function executeUpsert(): int
	{
		return $this->upsert()->send($this->em);
	}

	public function update(): BulkMessage
	{
		return $this->dialect->update($this->blueprint, $this->packets, $this->hooks);
	}

	public function executeUpdate(): int
	{
		return $this->update()->send($this->em);
	}

}
