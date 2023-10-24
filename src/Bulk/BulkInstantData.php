<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

final class BulkInstantData extends BulkData
{

	public function addValues(array $values, int|string|null $key = null): static
	{
		$this->checkFields($values);

		return $this->upsertValues($values, $key);
	}

}
