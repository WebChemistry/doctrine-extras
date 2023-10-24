<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;

abstract class BulkData
{

	public const SeverityException = 1;
	public const SeverityWarning = 2;
	public const SeverityIgnore = 3;

	/** @var BulkRow[] */
	protected array $rows = [];

	protected int $index = 0;

	/** @var self::SeverityException|self::SeverityWarning|self::SeverityIgnore */
	protected int $extraFieldSeverity = self::SeverityWarning;

	/** @var string[] */
	protected array $fieldsToCheck;

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
		$this->fieldsToCheck = array_keys(array_merge($this->fields, $this->metaFields));
	}

	/**
	 * @param self::SeverityException|self::SeverityWarning|self::SeverityIgnore $extraFieldSeverity
	 */
	public function setExtraFieldSeverity(int $extraFieldSeverity): static
	{
		$this->extraFieldSeverity = $extraFieldSeverity;

		return $this;
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
	abstract public function addValues(array $values, int|string|null $key = null): static;

	/**
	 * @param array<string, scalar|null> $values
	 * @param int|string|null $key
	 */
	protected function upsertValues(array $values, int|string|null $key = null): static
	{
		[$data, $dataParameters] = $this->processValues($values, $this->fields);
		[$meta, $metaParameters] = $this->processValues($values, $this->metaFields, '_meta');

		$fields = array_keys($values);
		$row = new BulkRow($data, $dataParameters, array_combine($fields, $fields), $meta, $metaParameters);

		if ($key === null) {
			$this->rows[] = $row;
		} else {
			if (isset($this->rows[$key])) {
				$this->rows[$key]->merge($row);
			} else {
				$this->rows[$key] = $row;
			}
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
	 * @return array{ array<string, string>, array<string, array{string, scalar|null, int}> }
	 */
	protected function processValues(array $values, array $fields, string $suffix = ''): array
	{
		if (!$fields) {
			return [[], []];
		}

		$parameters = [];
		$columns = [];

		foreach ($fields as $field => $column) {
			if (!array_key_exists($field, $values)) {
				continue;
			}

			$placeholder = sprintf('%s_%d%s', $field, $this->index, $suffix);

			$columns[$column] = $placeholder;
			$parameters[$column] = [$placeholder, ...$this->parseParameter($values[$field])];
		}

		return [$columns, $parameters];
	}

	/**
	 * @return array{scalar|null, int}
	 */
	protected function parseParameter(float|bool|int|string|null $value): array
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

	/**
	 * @param array<string, mixed> $fields
	 */
	protected function checkFields(array $fields, string|int|null $id = null): void
	{
		foreach ($this->fieldsToCheck as $field) {
			if (!array_key_exists($field, $fields)) {
				throw new InvalidArgumentException(sprintf('Field %s does not exist%s.', $field, $id !== null ? sprintf(' in %s.', $id) : ''));
			}

			unset($fields[$field]);
		}

		if (!$fields) {
			return;
		}

		if ($this->extraFieldSeverity === self::SeverityWarning) {
			trigger_error(
				sprintf('Extra fields %s%s.', implode(', ', array_keys($fields)),  $id !== null ? sprintf(' in %s.', $id) : ''),
				E_USER_WARNING
			);
		} elseif ($this->extraFieldSeverity === self::SeverityException) {
			throw new InvalidArgumentException(
				sprintf('Extra fields %s%s.', implode(', ', array_keys($fields)),  $id !== null ? sprintf(' in %s.', $id) : '')
			);
		}
	}

	public function __clone(): void
	{
		$this->rows = array_map(fn (BulkRow $row) => clone $row, $this->rows);
	}

}
