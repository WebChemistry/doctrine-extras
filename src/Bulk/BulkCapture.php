<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

final class BulkCapture
{

	/** @var array<string|int, array<string, scalar|null>> */
	private array $values = [];

	/**
	 * @param array<string, scalar|null> $values
	 */
	public function add(string|int $id, array $values): self
	{
		$this->values[$id] = array_merge($this->values[$id] ?? [], $values);

		return $this;
	}

	/**
	 * @return array<string|int, array<string, scalar|null>>
	 */
	public function getValues(): array
	{
		return $this->values;
	}

}
