<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping\ClassMetadata;
use Tester\Assert;
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
$blueprint = $schema->createBlueprint(new EntityManagerStub(['Foo' => $metadata]));

Assert::exception(function () use ($blueprint): void {
	$blueprint->createPacket(1, [
		'id' => 1,
		'firstName' => 'John',
	]);
}, InvalidArgumentException::class);

Assert::exception(function () use ($blueprint): void {
	$blueprint->createPacket(1, [
		'id' => 1,
		'firstName' => 'John',
		'ages' => 30,
	]);
}, InvalidArgumentException::class);

Assert::exception(function () use ($blueprint): void {
	$blueprint->createPacket(1, [
		'id' => 1,
		'firstName' => 'John',
		'age' => 30,
		'ages' => 30,
	]);
}, InvalidArgumentException::class);

$packet = $blueprint->createPacket(1, [
	'id' => 1,
	'firstName' => 'John',
	'age' => 30,
]);

Assert::count(1, $packet->ids);
Assert::count(2, $packet->fields);

Assert::same(['id', 'first_name', 'age'], $packet->getColumns());
Assert::same(['first_name', 'age'], $packet->getColumnsForFields());
Assert::same(['id'], $packet->getColumnsForIds());
Assert::same([':id_1', ':first_name_1', ':age_1'], $packet->getPlaceholders());
Assert::same([':first_name_1', ':age_1'], $packet->getPlaceholdersForFields());
Assert::same([':id_1'], $packet->getPlaceholdersForIds());

