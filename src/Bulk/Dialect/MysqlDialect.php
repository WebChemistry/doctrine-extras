<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Dialect;

use App\Debug\MemoryUsageDebugger;
use InvalidArgumentException;
use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;
use WebChemistry\DoctrineExtras\Bulk\Hook\BulkHook;
use WebChemistry\DoctrineExtras\Bulk\Message\BulkMessage;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;
use WebChemistry\DoctrineExtras\Bulk\Payload\BulkPayload;

final class MysqlDialect implements Dialect
{

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function insert(
		BulkBlueprint $blueprint,
		array $packets,
		array $hooks = [],
		bool $skipDuplications = false,
		array $options = [],
	): BulkMessage
	{
		return $this->buildInsert(
			$blueprint,
			$packets,
			fn (BulkHook $hook) => $hook->insert($blueprint, $packets, $skipDuplications),
			$hooks,
			$skipDuplications,
			options: $options,
		);
	}

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function insertIgnore(
		BulkBlueprint $blueprint,
		array $packets,
		array $hooks = [],
		array $options = [],
	): BulkMessage
	{
		return $this->buildInsert(
			$blueprint,
			$packets,
			fn (BulkHook $hook) => $hook->insertIgnore($blueprint, $packets),
			$hooks,
			ignore: true,
			options: $options,
		);
	}

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function upsert(
		BulkBlueprint $blueprint,
		array $packets,
		array $hooks = [],
		array $options = [],
	): BulkMessage
	{
		$message = $this->insert($blueprint, $packets, options: $options);

		$sql = sprintf('%s ON DUPLICATE KEY UPDATE %s', $message->sql, implode(', ', array_map(
			fn (string $column) => sprintf('%s = VALUES(%s)', $column, $column),
			$blueprint->getColumnNamesForFields(),
		)));

		return new BulkMessage($sql, $message->binds, array_map(
			fn (BulkHook $hook) => fn () => $hook->upsert($blueprint, $packets),
			$hooks,
		));
	}

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function update(
		BulkBlueprint $blueprint,
		array $packets,
		array $hooks = [],
		array $options = [],
	): BulkMessage
	{
		if (!$packets) {
			throw new InvalidArgumentException('No packets to upsert.');
		}

		$sql = '';

		$tableName = $blueprint->getTableName();
		$escape = $options[self::ColumnEscape] ?? false;
		$ignore = $options[self::Ignore] ?? false;

		foreach ($packets as $packet) {
			if ($ignore) {
				$fragment = sprintf('UPDATE IGNORE %s SET', $tableName);
			} else {
				$fragment = sprintf('UPDATE %s SET', $tableName);
			}

			foreach ($packet->fields as $field) {
				$fragment .= sprintf(
					' %s = %s,',
					$escape ? $this->escapeColumn($field->column) : $field->column,
					$packet->getPlaceholderFor($field),
				);
			}

			$fragment = sprintf('%s WHERE', substr($fragment, 0, -1));

			foreach ($packet->ids as $id) {
				$fragment .= sprintf(
					' %s = %s AND',
					$escape ? $this->escapeColumn($id->column) : $id->column,
					$packet->getPlaceholderFor($id),
				);
			}

			$fragment = substr($fragment, 0, -4) . ";\n";

			$sql .= $fragment;
		}

		$binds = [];

		foreach ($packets as $packet) {
			foreach ($packet->getBinds() as $id => $value) {
				$binds[$id] = $value;
			}
		}

		$sql = substr($sql, 0, -1);

		return new BulkMessage($sql, $binds, array_map(
			fn (BulkHook $hook) => fn () => $hook->update($blueprint, $packets),
			$hooks,
		));
	}



	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param callable(BulkHook $hook): void $hookCallback
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	private function buildInsert(
		BulkBlueprint $blueprint,
		array $packets,
		callable $hookCallback,
		array $hooks = [],
		bool $skipDuplications = false,
		bool $ignore = false,
		array $options = [],
	): BulkMessage
	{
		if (!$packets) {
			throw new InvalidArgumentException('No packets to upsert.');
		}

		$escape = $options[self::ColumnEscape] ?? false;
		$columnNames = $blueprint->getColumnNames();

		$sql = sprintf(
			'%s INTO %s (%s) VALUES',
			$ignore ? 'INSERT IGNORE' : 'INSERT',
			$blueprint->getTableName(),
			implode(', ', $escape ? $this->escapeColumns($columnNames) : $columnNames),
		);
		$binds = [];


		foreach ($packets as $i => $packet) {
			$sql .= sprintf(' (%s),', implode(', ', $packet->getPlaceholders()));

			foreach ($packet->getBinds() as $key => $bind) {
				$binds[$key] = $bind;
			}
		}

		$sql = substr($sql, 0, -1);

		if ($skipDuplications) {
			$sql = sprintf('%s ON DUPLICATE KEY UPDATE %s', $sql, implode(', ', array_map(
				fn (string $column) => sprintf('%s = %s', $escape ? $this->escapeColumn($column) : $column, $column),
				array_slice($blueprint->getColumnNamesForIds(), 0, 1),
			)));
		}

		return new BulkMessage($sql, $binds, array_map(
			fn (BulkHook $hook) => fn () => $hookCallback($hook),
			$hooks,
		));
	}

	/**
	 * @param string[] $columns
	 * @return string[]
	 */
	private function escapeColumns(array $columns): array
	{
		return array_map(
			fn (string $column) => sprintf('`%s`', $column),
			$columns,
		);
	}

	private function escapeColumn(string $column): string
	{
		return sprintf('`%s`', $column);
	}

}
