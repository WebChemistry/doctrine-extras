<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Payload;

use Doctrine\DBAL\ParameterType;

final class BulkPayload
{

	public function __construct(
		public readonly string $field,
		public readonly string $column,
		public readonly string|int|float|bool|null $value,
	)
	{
	}

	public function getBindValue(): string|int|bool|null
	{
		return is_float($this->value) ? (string) $this->value : $this->value;
	}

	public function getType(): int
	{
		if (is_string($this->value)) {
			return ParameterType::STRING;
		}

		if (is_bool($this->value)) {
			return ParameterType::BOOLEAN;
		}

		if (is_int($this->value)) {
			return ParameterType::INTEGER;
		}

		if (is_float($this->value)) {
			return ParameterType::STRING;
		}

		return ParameterType::NULL;
	}

}
