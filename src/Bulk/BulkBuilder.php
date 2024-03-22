<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Countable;
use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;
use WebChemistry\DoctrineExtras\Bulk\Dialect\Dialect;
use WebChemistry\DoctrineExtras\Bulk\Hook\BulkHook;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;

/**
 * @template TEntity of object
 */
final class BulkBuilder implements Countable
{

	private int $id = 0;

	/** @var BulkPacket[] */
	private array $packets = [];

	/**
	 * @param BulkBlueprint<TEntity> $blueprint
	 * @param BulkHook[] $hooks
	 */
	public function __construct(
		private EntityManagerInterface $em,
		private Dialect $dialect,
		private BulkBlueprint $blueprint,
		private array $hooks = [],
	)
	{
	}

	/**
	 * @param array<string, mixed> $values
	 * @return self<TEntity>
	 */
	public function add(array $values): self
	{
		$this->packets[] = $this->blueprint->createPacket($this->id++, $values);

		return $this;
	}

	/**
	 * @return self<TEntity>
	 */
	public function addCapture(BulkCapture $capture): self
	{
		foreach ($capture->getValues() as $values) {
			$this->add($values);
		}

		return $this;
	}

	public function count(): int
	{
		return count($this->packets);
	}

	public function clear(): void
	{
		$this->packets = [];
		$this->id = 0;
	}

	/**
	 * @return Bulk<TEntity>
	 */
	public function build(): Bulk
	{
		return new Bulk($this->em, $this->blueprint, $this->dialect, $this->packets, $this->hooks);
	}

	/**
	 * @return Bulk<TEntity>|null
	 */
	public function buildIfNotEmpty(): ?Bulk
	{
		if (!$this->packets) {
			return null;
		}

		return $this->build();
	}

}
