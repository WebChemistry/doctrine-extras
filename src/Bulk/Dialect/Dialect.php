<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Dialect;

use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;
use WebChemistry\DoctrineExtras\Bulk\Hook\BulkHook;
use WebChemistry\DoctrineExtras\Bulk\Message\BulkMessage;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;

interface Dialect
{

	public const ColumnEscape = 'columnEscape';
	
	public const Ignore = 'ignore';

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function insert(BulkBlueprint $blueprint, array $packets, array $hooks = [], bool $skipDuplications = false, array $options = []): BulkMessage;

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function insertIgnore(BulkBlueprint $blueprint, array $packets, array $hooks = [], array $options = []): BulkMessage;

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function upsert(BulkBlueprint $blueprint, array $packets, array $hooks = [], array $options = []): BulkMessage;

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 * @param mixed[] $options
	 */
	public function update(BulkBlueprint $blueprint, array $packets, array $hooks = [], array $options = []): BulkMessage;

}
