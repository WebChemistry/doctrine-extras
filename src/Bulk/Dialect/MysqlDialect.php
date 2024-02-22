<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Dialect;

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
	 */
	public function insert(BulkBlueprint $blueprint, array $packets, array $hooks = [], bool $skipDuplications = false): BulkMessage
	{
		return $this->buildInsert(
			$blueprint,
			$packets,
			fn (BulkHook $hook) => $hook->insert($blueprint, $packets, $skipDuplications),
			$hooks,
			$skipDuplications,
		);
	}

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 */
	public function insertIgnore(BulkBlueprint $blueprint, array $packets, array $hooks = []): BulkMessage
	{
		return $this->buildInsert(
			$blueprint,
			$packets,
			fn (BulkHook $hook) => $hook->insertIgnore($blueprint, $packets),
			$hooks,
			ignore: true,
		);
	}

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 */
	public function upsert(BulkBlueprint $blueprint, array $packets, array $hooks = []): BulkMessage
	{
		$message = $this->insert($blueprint, $packets);

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
	 */
	public function update(BulkBlueprint $blueprint, array $packets, array $hooks = []): BulkMessage
	{
		if (!$packets) {
			throw new InvalidArgumentException('No packets to upsert.');
		}

		$sql = '';

		$tableName = $blueprint->getTableName();

		foreach ($packets as $packet) {
			$fragment = sprintf('UPDATE %s SET', $tableName);

			foreach ($packet->fields as $field) {
				$fragment .= sprintf(' %s = %s,', $field->column, $packet->getPlaceholderFor($field));
			}

			$fragment = sprintf('%s WHERE', substr($fragment, 0, -1));

			foreach ($packet->ids as $id) {
				$fragment .= sprintf(' %s = %s AND', $id->column, $packet->getPlaceholderFor($id));
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
	 */
	private function buildInsert(
		BulkBlueprint $blueprint,
		array $packets,
		callable $hookCallback,
		array $hooks = [],
		bool $skipDuplications = false,
		bool $ignore = false,
	): BulkMessage
	{
		if (!$packets) {
			throw new InvalidArgumentException('No packets to upsert.');
		}

		$sql = sprintf(
			'%s INTO %s (%s) VALUES',
			$ignore ? 'INSERT IGNORE' : 'INSERT',
			$blueprint->getTableName(),
			implode(', ', $blueprint->getColumnNames()),
		);
		$binds = [];

		foreach ($packets as $packet) {
			$sql .= sprintf(' (%s),', implode(', ', $packet->getPlaceholders()));
			$binds = array_merge($binds, $packet->getBinds());
		}

		$sql = substr($sql, 0, -1);

		if ($skipDuplications) {
			$sql = sprintf('%s ON DUPLICATE KEY UPDATE %s', $sql, implode(', ', array_map(
				fn (string $column) => sprintf('%s = %s', $column, $column),
				array_slice($blueprint->getColumnNamesForIds(), 0, 1),
			)));
		}

		return new BulkMessage($sql, $binds, array_map(
			fn (BulkHook $hook) => fn () => $hookCallback($hook),
			$hooks,
		));
	}

}
