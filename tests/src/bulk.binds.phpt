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
	'created' => [
		'fieldName' => 'created',
		'columnName' => 'created',
		'type' => 'datetime',
	],
];

$schema = new BulkSchema('Foo', ['firstName', 'age', 'created']);
$blueprint = $schema->createBlueprint($em = new EntityManagerStub(['Foo' => $metadata]));
$builder = new BulkBuilder($em, new MysqlDialect(), $blueprint);
$builder->add([
	'id' => 1,
	'firstName' => 'John',
	'age' => 30,
	'created' => new DateTime('2021-01-01'),
]);

Assert::same(['id_0' => [1, 1], 'first_name_0' => ['John', 2], 'age_0' => [30, 1], 'created_0' => ['2021-01-01 00:00:00', 2],], $builder->build()->insert()->binds);
