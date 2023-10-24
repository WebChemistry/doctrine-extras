<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Dialect;

use WebChemistry\DoctrineExtras\Bulk\BulkData;

interface Dialect
{

	/**
	 * @param array{
	 *	   skipConflicts?: bool,
	 *     upsert?: bool,
	 *     replace?: bool,
	 * } $options
	 */
	public function createInsert(BulkData $data, array $options = []): string;

	/**
	 * @param mixed[] $options
	 */
	public function createUpdate(BulkData $data, array $options = []): string;

}
