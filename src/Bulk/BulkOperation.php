<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Countable;
use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Bulk\Dialect\Dialect;

abstract class BulkOperation implements Countable
{

	public function __construct(
		protected EntityManagerInterface $em,
		protected BulkData $data,
		protected Dialect $dialect,
	)
	{
	}

	/**
	 * @param array<string, scalar|null> $values
	 * @param int|string|null $key
	 */
	public function addValues(array $values, int|string|null $key = null): self
	{
		$this->data->addValues($values, $key);

		return $this;
	}

	abstract public function getSql(): string;

	public function count(): int
	{
		return count($this->data->getRows());
	}

	public function execute(): int
	{
		$stmt = $this->em->getConnection()->prepare($this->getSql());

		foreach ($this->data->getRows() as $row) {
			foreach ($row->dataParameters as $args) {
				$stmt->bindValue(...$args);
			}

			foreach ($row->metaParameters as $args) {
				$stmt->bindValue(...$args);
			}
		}

		return $stmt->executeStatement();
	}

}
