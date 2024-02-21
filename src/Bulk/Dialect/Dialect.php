<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Dialect;

use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;
use WebChemistry\DoctrineExtras\Bulk\Hook\BulkHook;
use WebChemistry\DoctrineExtras\Bulk\Message\BulkMessage;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;

interface Dialect
{

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 */
	public function insert(BulkBlueprint $blueprint, array $packets, array $hooks = [], bool $skipDuplications = false): BulkMessage;

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 */
	public function upsert(BulkBlueprint $blueprint, array $packets, array $hooks = []): BulkMessage;

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 * @param BulkHook[] $hooks
	 */
	public function update(BulkBlueprint $blueprint, array $packets, array $hooks = []): BulkMessage;

}
