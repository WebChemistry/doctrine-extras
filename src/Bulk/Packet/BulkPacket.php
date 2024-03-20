<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Packet;

use LogicException;
use Nette\Utils\Arrays;
use WebChemistry\DoctrineExtras\Bulk\Payload\BulkPayload;

final class BulkPacket
{

	/** @var BulkPayload[] */
	private array $all;

	/**
	 * @param BulkPayload[] $ids
	 * @param BulkPayload[] $fields
	 */
	public function __construct(
		public readonly int $id,
		public readonly array $ids,
		public readonly array $fields,
	)
	{
		$this->all = array_merge($ids, $fields);
	}

	/**
	 * Get map of columns and values including ids
	 * @return array<string, scalar|null> column => value
	 */
	public function getColumnValueMap(): array
	{
		$map = [];

		foreach ($this->all as $payload) {
			$map[$payload->column] = $payload->value;
		}

		return $map;
	}

	/**
	 * Get map of fields and values including ids
	 * @return array<string, scalar|null> field => value
	 */
	public function getFieldValueMap(): array
	{
		$map = [];

		foreach ($this->all as $payload) {
			$map[$payload->field] = $payload->value;
		}

		return $map;
	}

	public function getSingleId(): int|string|float|bool
	{
		$first = Arrays::first($this->ids);

		if ($first === null) {
			throw new LogicException('Bulk packet has no id');
		}

		if (count($this->ids) > 1) {
			throw new LogicException('Bulk packet has more than one id');
		}

		$value = $first->value;

		if ($value === null) {
			throw new LogicException('Bulk packet has null id');
		}

		return $value;
	}

	/**
	 * @return string[]
	 */
	public function getPlaceholders(): array
	{
		return array_map(fn (BulkPayload $payload) => sprintf(':%s_%d', $payload->column, $this->id), $this->all);
	}

	public function getPlaceholderFor(BulkPayload $payload): string
	{
		return sprintf(':%s_%d', $payload->column, $this->id);
	}

	/**
	 * @return array<string, array{scalar|null, int}>
	 */
	public function getBinds(): array
	{
		$binds = [];

		foreach ($this->all as $payload) {
			$binds[sprintf('%s_%d', $payload->column, $this->id)] = [$payload->getBindValue(), $payload->getType()];
		}

		return $binds;
	}

	/**
	 * @return string[]
	 */
	public function getPlaceholdersForIds(): array
	{
		return array_map(fn (BulkPayload $payload) => sprintf(':%s_%d', $payload->column, $this->id), $this->ids);
	}

	/**
	 * @return string[]
	 */
	public function getPlaceholdersForFields(): array
	{
		return array_map(fn (BulkPayload $payload) => sprintf(':%s_%d', $payload->column, $this->id), $this->fields);
	}

	/**
	 * @return string[]
	 */
	public function getColumns(): array
	{
		return array_map(fn (BulkPayload $payload) => $payload->column, $this->all);
	}

	/**
	 * @return string[]
	 */
	public function getColumnsForIds(): array
	{
		return array_map(fn (BulkPayload $payload) => $payload->column, $this->ids);
	}

	/**
	 * @return string[]
	 */
	public function getColumnsForFields(): array
	{
		return array_map(fn (BulkPayload $payload) => $payload->column, $this->fields);
	}

}
