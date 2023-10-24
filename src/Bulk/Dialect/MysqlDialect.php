<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Dialect;

use LogicException;
use WebChemistry\DoctrineExtras\Bulk\BulkData;
use WebChemistry\DoctrineExtras\Bulk\BulkRow;

final class MysqlDialect implements Dialect
{

	/**
	 * @param array{
	 *	   skipConflicts?: bool,
	 *     upsert?: bool,
	 *     replace?: bool,
	 * } $options
	 */
	public function createInsert(BulkData $data, array $options = []): string
	{
		if (($options['replace'] ?? false) === true) {
			return $this->createReplace($data);
		}

		if (($options['skipConflicts'] ?? false) === true) {
			return sprintf(
				'INSERT IGNORE INTO %s (%s) VALUES %s',
				$data->getTableName(),
				implode(', ', $data->getColumns()),
				$this->toPlaceholders($data->getRows()),
			);
		}

		$base = sprintf(
			'INSERT INTO %s (%s) VALUES %s',
			$data->getTableName(),
			implode(', ', $data->getColumns()),
			$this->toPlaceholders($data->getRows()),
		);

		if (($options['upsert'] ?? false) === true) {
			return sprintf(
				'%s ON DUPLICATE KEY UPDATE %s',
				$base,
				$this->buildDuplicateKeyUpdate($data->getMetaColumns() ?: $data->getColumns()),
			);
		}

		return $base;
	}

	/**
	 * @param BulkData $data
	 * @param mixed[] $options
	 * @return string
	 */
	public function createUpdate(BulkData $data, array $options = []): string
	{
		$sql = '';

		foreach ($data->getRows() as $row) {
			if (!$row->meta) {
				throw new LogicException('Meta columns must be set for update.');
			}

			$sql .= sprintf(
				"UPDATE %s SET %s WHERE %s;\n",
				$data->getTableName(),
				$this->toAssigns($row->data),
				$this->toAssigns($row->meta),
			);
		}

		return rtrim($sql);
	}

	/**
	 * @param array<string, string> $rows placeholder => column
	 */
	private function toAssigns(array $rows): string
	{
		return implode(', ', array_map(
			fn (string $placeholder, string $column) => sprintf('%s = :%s', $column, $placeholder),
			array_keys($rows),
			$rows,
		));
	}

	/**
	 * @param BulkRow[] $rows
	 */
	private function toPlaceholders(array $rows): string
	{
		return implode(', ', array_map(
			fn (BulkRow $row) => sprintf('(:%s)', implode(', :', array_keys($row->data))),
			$rows,
		));
	}

	/**
	 * @param string[] $columns
	 */
	private function buildDuplicateKeyUpdate(array $columns): string
	{
		return implode(', ', array_map(
			fn (string $column) => sprintf('%s = VALUES(%s)', $column, $column),
			$columns,
		));
	}

	private function createReplace(BulkData $data): string
	{
		return sprintf(
			'REPLACE INTO %s (%s) VALUES %s',
			$data->getTableName(),
			implode(', ', $data->getColumns()),
			$this->toPlaceholders($data->getRows()),
		);
	}

}
