<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Countable;

final class BulkCapture implements Countable
{

	/** @var array<string|int, array<string, mixed>> */
	private array $values = [];

	/**
	 * @param array<string, mixed> $values
	 */
	public function add(string|int $id, array $values): self
	{
		$this->values[$id] = array_merge($this->values[$id] ?? [], $values);

		return $this;
	}

	/**
	 * @return array<string|int, array<string, mixed>>
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	public function count(): int
	{
		return count($this->values);
	}

}
