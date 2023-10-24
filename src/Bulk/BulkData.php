<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;

final class BulkData
{

	/** @var BulkRow[] */
	private array $rows = [];

	private int $index = 0;

	/**
	 * @param ClassMetadata<object> $metadata
	 * @param array<string, string> $fields fieldName => columName
	 * @param array<string, string> $metaFields fieldName => columName
	 */
	public function __construct(
		private ClassMetadata $metadata,
		private array $fields,
		private array $metaFields = [],
	)
	{
	}

	public function getTableName(): string
	{
		return $this->metadata->getTableName();
	}

	/**
	 * @return string[]
	 */
	public function getColumns(): array
	{
		return $this->fields;
	}

	/**
	 * @return string[]
	 */
	public function getMetaColumns(): array
	{
		return $this->metaFields;
	}

	/**
	 * @param array<string, scalar|null> $values
	 * @param int|string|null $key
	 */
	public function addValues(array $values, int|string|null $key = null): self
	{
		[$data, $dataParameters] = $this->processValues($values, $this->fields);
		[$meta, $metaParameters] = $this->processValues($values, $this->metaFields, '_meta');

		$row = new BulkRow($data, $dataParameters, $meta, $metaParameters);

		if ($key === null) {
			$this->rows[] = $row;
		} else {
			$this->rows[$key] = $row;
		}

		$this->index++;

		return $this;
	}

	/**
	 * @return BulkRow[]
	 */
	public function getRows(): array
	{
		return $this->rows;
	}

	/**
	 * @param array<string, scalar|null> $values
	 * @param array<string, string> $fields
	 * @return array{ array<string, string>, array{string, scalar|null, int}[] }
	 */
	private function processValues(array $values, array $fields, string $suffix = ''): array
	{
		if (!$fields) {
			return [[], []];
		}

		$parameters = [];
		$columns = [];

		foreach ($fields as $field => $column) {
			if (!array_key_exists($field, $values)) {
				throw new InvalidArgumentException(sprintf('Field %s does not exist.', $field));
			}

			$columns[sprintf('%s_%d%s', $field, $this->index, $suffix)] = $column;
			$parameters[] = [sprintf('%s_%d%s', $field, $this->index, $suffix), ...$this->parseParameter($values[$field])];
		}

		return [$columns, $parameters];
	}

	/**
	 * @return array{scalar|null, int}
	 */
	private function parseParameter(float|bool|int|string|null $value): array
	{
		if (is_string($value)) {
			return [$value, ParameterType::STRING];
		}

		if (is_bool($value)) {
			return [$value, ParameterType::BOOLEAN];
		}

		if (is_int($value)) {
			return [$value, ParameterType::INTEGER];
		}

		if (is_float($value)) {
			return [(string) $value, ParameterType::STRING];
		}

		return [$value, ParameterType::NULL];
	}

}
