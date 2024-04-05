<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Message;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;

final class BulkMessage
{

	/**
	 * @param array<string, array{ scalar|null, int }> $binds
	 * @param array<callable(): void> $hooks
	 */
	public function __construct(
		public readonly string $sql,
		public readonly array $binds,
		private array $hooks = [],
	)
	{
	}

	public function bindTo(Statement $statement): void
	{
		foreach ($this->binds as $key => [$value, $type]) {
			$statement->bindValue($key, $value, $type);
		}
	}

	public function send(EntityManagerInterface $em): void
	{
		$stmt = $em->getConnection()->prepare($this->sql);

		$this->bindTo($stmt);

		$stmt->executeQuery()->free();

		foreach ($this->hooks as $hook) {
			$hook();
		}
	}

}
