<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

final class BulkLateData extends BulkData
{

	private bool $valid = true;

	public function addValues(array $values, int|string|null $key = null): static
	{
		$this->valid = false;

		return $this->upsertValues($values, $key);
	}

	/**
	 * @return BulkRow[]
	 */
	public function getRows(): array
	{
		if (!$this->valid) {
			$this->checkRows();

			$this->valid = true;
		}

		return $this->rows;
	}

	private function checkRows(): void
	{
		foreach ($this->rows as $index => $row) {
			$this->checkFields($row->fields, $index);
		}
	}

}
