<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

final class BulkInsert extends BulkOperation
{

	private bool $upsert = false;

	private bool $skipConflicts = false;

	private bool $replace = false;

	public function setReplace(bool $replace): static
	{
		$this->replace = $replace;

		return $this;
	}

	public function setUpsert(bool $upsert): static
	{
		$this->upsert = $upsert;

		return $this;
	}

	public function setSkipConflicts(bool $skipConflicts): static
	{
		$this->skipConflicts = $skipConflicts;

		return $this;
	}

	public function getSql(): string
	{
		return $this->dialect->createInsert($this->data, [
			'upsert' => $this->upsert,
			'skipConflicts' => $this->skipConflicts,
			'replace' => $this->replace,
		]);
	}

}
