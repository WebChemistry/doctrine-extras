<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

declare(strict_types=1);

use WebChemistry\DoctrineExtras\Identity\EntityIdentity;
use WebChemistry\DoctrineExtras\Identity\EntityWithIdentity;

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}


// configure environment
Tester\Environment::setup();

function getTempDir(): string
{
	$dir = __DIR__ . '/tmp/' . getmypid();

	if (empty($GLOBALS['\\lock'])) {
		// garbage collector
		$GLOBALS['\\lock'] = $lock = fopen(__DIR__ . '/lock', 'w');
		if (rand(0, 100)) {
			flock($lock, LOCK_SH);
			@mkdir(dirname($dir));
		} elseif (flock($lock, LOCK_EX)) {
			Tester\Helpers::purge(dirname($dir));
		}

		@mkdir($dir);
	}

	return $dir;
}


function test(string $title, callable $function): void
{
	$function();
}

final class FirstEntity implements EntityWithIdentity {

	public function __construct(
		private string|int $id,
	)
	{
	}

	public function identity(): EntityIdentity
	{
		return new EntityIdentity(self::class, $this->id);
	}

}

final class SecondEntity implements EntityWithIdentity {

	public function __construct(
		private string|int $id,
	)
	{
	}

	public function identity(): EntityIdentity
	{
		return new EntityIdentity(self::class, $this->id);
	}

}

final class ComplexEntity implements EntityWithIdentity {

	public function __construct(
		private int|string $firstId,
		private int|string $secondId,
	)
	{
	}

	public function identity(): EntityIdentity
	{
		return new EntityIdentity(self::class, $this->firstId, $this->secondId);
	}

}
