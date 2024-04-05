<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Countable;
use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;
use WebChemistry\DoctrineExtras\Bulk\Dialect\Dialect;
use WebChemistry\DoctrineExtras\Bulk\Hook\BulkHook;
use WebChemistry\DoctrineExtras\Bulk\Message\BulkMessage;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;

/**
 * @template TEntity of object
 */
final class Bulk implements Countable
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

	/**
	 * @param mixed[] $options
	 */
	public function insert(bool $skipDuplications = false, array $options = []): BulkMessage
	{
		return $this->dialect->insert($this->blueprint, $this->packets, $this->hooks, $skipDuplications, $options);
	}

	/**
	 * @param mixed[] $options
	 */
	public function executeInsert(bool $skipDuplications = false, array $options = []): void
	{
		$this->insert($skipDuplications, $options)->send($this->em);
	}

	/**
	 * @param mixed[] $options
	 */
	public function insertIgnore(array $options = []): BulkMessage
	{
		return $this->dialect->insertIgnore($this->blueprint, $this->packets, $this->hooks, $options);
	}

	/**
	 * @param mixed[] $options
	 */
	public function executeInsertIgnore(array $options = []): void
	{
		$this->insertIgnore($options)->send($this->em);
	}

	/**
	 * @param mixed[] $options
	 */
	public function upsert(array $options = []): BulkMessage
	{
		return $this->dialect->upsert($this->blueprint, $this->packets, $this->hooks, $options);
	}

	/**
	 * @param mixed[] $options
	 */
	public function executeUpsert(array $options = []): void
	{
		$this->upsert($options)->send($this->em);
	}

	/**
	 * @param mixed[] $options
	 */
	public function update(array $options = []): BulkMessage
	{
		return $this->dialect->update($this->blueprint, $this->packets, $this->hooks, $options);
	}

	/**
	 * @param mixed[] $options
	 */
	public function executeUpdate(array $options = []): void
	{
		$this->update($options)->send($this->em);
	}

	public function count(): int
	{
		return count($this->packets);
	}

}
