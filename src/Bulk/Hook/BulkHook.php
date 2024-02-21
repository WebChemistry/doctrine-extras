<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Hook;

use WebChemistry\DoctrineExtras\Bulk\Blueprint\BulkBlueprint;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;

interface BulkHook
{

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 */
	public function insert(BulkBlueprint $blueprint, array $packets, bool $skipDuplications = false): void;

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 */
	public function upsert(BulkBlueprint $blueprint, array $packets): void;

	/**
	 * @template T of object
	 * @param BulkBlueprint<T> $blueprint
	 * @param BulkPacket[] $packets
	 */
	public function update(BulkBlueprint $blueprint, array $packets): void;

}
