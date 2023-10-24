<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;

final class BulkData
{

	public const SeverityException = 1;
	public const SeverityWarning = 2;
	public const SeverityIgnore = 3;

	/** @var BulkRow[] */
	private array $rows = [];

	private int $index = 0;

	/** @var self::SeverityException|self::SeverityWarning|self::SeverityIgnore */
	private int $extraFieldSeverity = self::SeverityWarning;

	/** @var string[] */
	private array $fieldsToCheck;

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
	public function addValues(array $values, int|string|null $key = null): self
	{
		$this->checkFields($values);

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
			$columns[sprintf('%s_%d%s', $field, $this->index, $suffix)] = $column;
			$parameters[] = [sprintf('%s_%d%s', $field, $this->index, $suffix), ...$this->parseParameter($values[$field])];

			unset($values[$field]);
		}

		if ($values) {
			if ($this->extraFieldSeverity === self::SeverityWarning) {
				trigger_error(
					sprintf('Extra fields %s in %s.', implode(', ', array_keys($values)), $this->metadata->getName()),
					E_USER_WARNING
				);
			} elseif ($this->extraFieldSeverity === self::SeverityException) {
				throw new InvalidArgumentException(
					sprintf('Extra fields %s in %s.', implode(', ', array_keys($values)), $this->metadata->getName())
				);
			}
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

	/**
	 * @param array<string, scalar|null> $values
	 */
	private function checkFields(array $values): void
	{
		foreach ($this->fieldsToCheck as $field) {
			if (!array_key_exists($field, $values)) {
				throw new InvalidArgumentException(sprintf('Field %s does not exist.', $field));
			}

			unset($values[$field]);
		}

		if (!$values) {
			return;
		}

		if ($this->extraFieldSeverity === self::SeverityWarning) {
			trigger_error(
				sprintf('Extra fields %s in %s.', implode(', ', array_keys($values)), $this->metadata->getName()),
				E_USER_WARNING
			);
		} elseif ($this->extraFieldSeverity === self::SeverityException) {
			throw new InvalidArgumentException(
				sprintf('Extra fields %s in %s.', implode(', ', array_keys($values)), $this->metadata->getName())
			);
		}
	}

}
