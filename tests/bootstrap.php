<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

declare(strict_types=1);

use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
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

class EntityManagerStub implements EntityManagerInterface {

	public function __construct(
		private array $metadata,
	)
	{
	}

	public function refresh(object $object, ?int $lockMode = null): void
	{
	}

	public function getMetadataFactory()
	{
	}

	public function getRepository($className)
	{
	}

	public function getCache()
	{
	}

	public function getConnection()
	{
		return new class {
			public function getDatabasePlatform()
			{
				return new MySQL80Platform();
			}
		};
	}

	public function getExpressionBuilder()
	{
	}

	public function beginTransaction()
	{
	}

	public function transactional($func)
	{
	}

	public function commit()
	{
	}

	public function rollback()
	{
	}

	public function createQuery($dql = '')
	{
	}

	public function createNamedQuery($name)
	{
	}

	public function createNativeQuery($sql, ResultSetMapping $rsm)
	{
	}

	public function createNamedNativeQuery($name)
	{
	}

	public function createQueryBuilder()
	{
	}

	public function getReference($entityName, $id)
	{
	}

	public function getPartialReference($entityName, $identifier)
	{
	}

	public function close()
	{
	}

	public function copy($entity, $deep = false)
	{
	}

	public function lock($entity, $lockMode, $lockVersion = null)
	{
	}

	public function getEventManager()
	{
	}

	public function getConfiguration()
	{
	}

	public function isOpen()
	{
	}

	public function getUnitOfWork()
	{
	}

	public function getHydrator($hydrationMode)
	{
	}

	public function newHydrator($hydrationMode)
	{
	}

	public function getProxyFactory()
	{
	}

	public function getFilters()
	{
	}

	public function isFiltersStateClean()
	{
	}

	public function hasFilters()
	{
	}

	public function getClassMetadata($className)
	{
		return $this->metadata[$className];
	}

	public function find(string $className, $id)
	{
	}

	public function persist(object $object)
	{
	}

	public function remove(object $object)
	{
	}

	public function clear()
	{
	}

	public function detach(object $object)
	{
	}

	public function flush()
	{
	}

	public function initializeObject(object $obj)
	{
	}

	public function contains(object $object)
	{
	}

}
