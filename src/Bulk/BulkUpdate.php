<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

final class BulkUpdate extends BulkOperation
{

	public function getSql(): string
	{
		return $this->dialect->createUpdate($this->data);
	}

}
