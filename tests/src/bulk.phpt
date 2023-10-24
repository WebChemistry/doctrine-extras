<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping\ClassMetadata;
use Tester\Assert;
use WebChemistry\DoctrineExtras\Bulk\BulkData;
use WebChemistry\DoctrineExtras\Bulk\Dialect\MysqlDialect;

require __DIR__ . '/../bootstrap.php';

$metadata = new ClassMetadata('Foo');
$metadata->setPrimaryTable([
	'name' => 'foo',
]);
$data = new BulkData($metadata, ['id' => 'id', 'firstName' => 'first_name'], ['id' => 'id']);
$data->setExtraFieldSeverity($data::SeverityException);

$data->addValues(['id' => 1, 'firstName' => 'John']);
$data->addValues(['id' => 2, 'firstName' => 'Jane']);
$dialect = new MysqlDialect();

Assert::same('INSERT INTO foo (id, first_name) VALUES (:id_0, :firstName_0), (:id_1, :firstName_1)', $dialect->createInsert($data));
Assert::same('INSERT IGNORE INTO foo (id, first_name) VALUES (:id_0, :firstName_0), (:id_1, :firstName_1)', $dialect->createInsert($data, [
	'skipConflicts' => true,
]));
Assert::same('REPLACE INTO foo (id, first_name) VALUES (:id_0, :firstName_0), (:id_1, :firstName_1)', $dialect->createInsert($data, [
	'replace' => true,
]));

Assert::same('UPDATE foo SET id = :id_0, first_name = :firstName_0 WHERE id = :id_0_meta;
UPDATE foo SET id = :id_1, first_name = :firstName_1 WHERE id = :id_1_meta;', $dialect->createUpdate($data));

// missing values
Assert::exception(fn () => $data->addValues(['id' => 3]), InvalidArgumentException::class);

// extra values
Assert::exception(fn () => $data->addValues(['id' => 3, 'firstName' => 'Alice', 'lastName' => 'Smith']), InvalidArgumentException::class);
