## Doctrine Bulk

```php
$factory = new BulkFactory($em);

// insert
$bulk = $factory->createInsert(Entity::class, ['id', 'firstName']);

$bulk->setReplace(true); // REPLACE INTO ...
$bulk->setSkipConflicts(true); // INSERT IGNORE ...
$bulk->setUpsert(true); // INSERT INTO ... ON DUPLICATE KEY UPDATE

$bulk->addValues([
    'id' => 1,
    'firstName' => 'Jane',
]);
$bulk->execute();

// update
$bulk = $factory->createUpdate(Entity::class, ['firstName'], ['id']); // updates only firstName, field id is in where clause
$bulk->addValues([
    'id' => 1,
    'firstName' => 'Jane',
]);
$bulk->execute();
```
