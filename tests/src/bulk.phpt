<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping\ClassMetadata;
use Tester\Assert;
use WebChemistry\DoctrineExtras\Bulk\BulkBuilder;
use WebChemistry\DoctrineExtras\Bulk\Dialect\MysqlDialect;
use WebChemistry\DoctrineExtras\Bulk\Schema\BulkSchema;

require __DIR__ . '/../bootstrap.php';

$metadata = new ClassMetadata('Foo');
$metadata->setPrimaryTable([
	'name' => 'foo',
]);
$metadata->identifier = ['id'];
$metadata->fieldMappings = [
	'id' => [
		'fieldName' => 'id',
		'columnName' => 'id',
		'type' => 'integer',
	],
	'firstName' => [
		'fieldName' => 'firstName',
		'columnName' => 'first_name',
		'type' => 'string',
	],
	'age' => [
		'fieldName' => 'age',
		'columnName' => 'age',
		'type' => 'integer',
	],
];

$schema = new BulkSchema('Foo', ['firstName', 'age']);
$blueprint = $schema->createBlueprint($em = new EntityManagerStub(['Foo' => $metadata]));
$builder = new BulkBuilder($em, new MysqlDialect(), $blueprint);
$builder->add([
	'id' => 1,
	'firstName' => 'John',
	'age' => 30,
]);

Assert::same('INSERT INTO foo (id, first_name, age) VALUES (:id_0, :first_name_0, :age_0)', $builder->build()->insert()->sql);

$builder->add([
	'id' => 2,
	'firstName' => 'John',
	'age' => 31,
]);

// insert
Assert::same('INSERT INTO foo (id, first_name, age) VALUES (:id_0, :first_name_0, :age_0), (:id_1, :first_name_1, :age_1)', $builder->build()->insert()->sql);
// insert ignore
Assert::same('INSERT IGNORE INTO foo (id, first_name, age) VALUES (:id_0, :first_name_0, :age_0), (:id_1, :first_name_1, :age_1)', $builder->build()->insertIgnore()->sql);
// insert, skip duplications
Assert::same('INSERT INTO foo (id, first_name, age) VALUES (:id_0, :first_name_0, :age_0), (:id_1, :first_name_1, :age_1) ON DUPLICATE KEY UPDATE id = id', $builder->build()->insert(true)->sql);
// upsert
Assert::same('INSERT INTO foo (id, first_name, age) VALUES (:id_0, :first_name_0, :age_0), (:id_1, :first_name_1, :age_1) ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), age = VALUES(age)', $builder->build()->upsert()->sql);
// update
Assert::same('UPDATE foo SET first_name = :first_name_0, age = :age_0 WHERE id = :id_0;
UPDATE foo SET first_name = :first_name_1, age = :age_1 WHERE id = :id_1;', $builder->build()->update()->sql);

// multiple ids
$metadata->identifier = ['id', 'firstName'];

$builder = new BulkBuilder($em, new MysqlDialect(), $schema->createBlueprint($em = new EntityManagerStub(['Foo' => $metadata])));
$builder->add([
	'id' => 1,
	'firstName' => 'John',
	'age' => 30,
]);

Assert::same('UPDATE foo SET first_name = :first_name_0, age = :age_0 WHERE id = :id_0 AND first_name = :first_name_0;', $builder->build()->update()->sql);
