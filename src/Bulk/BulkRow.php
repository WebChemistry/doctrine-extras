<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\DBAL\ParameterType;

final class BulkRow
{

	/**
	 * @param array<string, string> $data column => placeholder
	 * @param array{string, scalar|null, int}[] $dataParameters
	 * @param array<string, string> $fields
	 * @param array<string, string> $meta column => placeholder
	 * @param array{string, scalar|null, int}[] $metaParameters
	 */
	public function __construct(
		public array $data,
		public array $dataParameters,
		public array $fields,
		public array $meta = [],
		public array $metaParameters = [],
	)
	{
	}

	public function merge(BulkRow $row): void
	{
		$this->data = array_merge($this->data, $row->data);
		$this->dataParameters = array_merge($this->dataParameters, $row->dataParameters);
		$this->fields = array_merge($this->fields, $row->fields);
		$this->meta = array_merge($this->meta, $row->meta);
		$this->metaParameters = array_merge($this->metaParameters, $row->metaParameters);
	}

}
