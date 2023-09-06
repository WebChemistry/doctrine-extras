<?php declare(strict_types = 1);

use Tester\Assert;
use WebChemistry\DoctrineExtras\Map\EntityMap;

require __DIR__ . '/../bootstrap.php';

$map = EntityMap::fromEntries([
	[new FirstEntity(1), 'first'],
	[new FirstEntity(2), 'second'],
	[new FirstEntity(3), 'third'],
]);

Assert::same($map[new FirstEntity(1)], 'first');
Assert::same($map[new FirstEntity(2)], 'second');
Assert::same($map[(new FirstEntity(3))->identity()], 'third');
Assert::throws(fn () => $map[new FirstEntity(4)], OutOfBoundsException::class);
Assert::throws(fn () => $map[new SecondEntity(3)], OutOfBoundsException::class);

Assert::same($map->getOr(new FirstEntity(4), 'foo'), 'foo');
Assert::null($map->getNullable(new FirstEntity(4)));
