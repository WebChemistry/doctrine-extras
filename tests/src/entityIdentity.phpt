<?php declare(strict_types = 1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

Assert::same('FirstEntity(1)', (string) (new FirstEntity(1))->identity());

Assert::same('ComplexEntity(1,2)', (string) (new ComplexEntity(1, 2))->identity());

Assert::same('ComplexEntity(1,2)', (string) (new ComplexEntity('1', '2'))->identity());
