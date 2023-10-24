<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use InvalidArgumentException;
use WebChemistry\DoctrineExtras\Bulk\Dialect\Dialect;
use WebChemistry\DoctrineExtras\Bulk\Dialect\MysqlDialect;

final class BulkFactory
{

	private MysqlDialect $dialect;

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	/**
	 * @param class-string $className
	 * @param string[] $fields
	 * @param string[] $metaFields
	 */
	public function createUpdate(string $className, array $fields, array $metaFields): BulkUpdate
	{
		$metadata = $this->em->getClassMetadata($className);

		return new BulkUpdate(
			$this->em,
			new BulkInstantData(
				$metadata,
				$this->createColumnFieldArray($fields, $metadata),
				$this->createColumnFieldArray($metaFields, $metadata),
			),
			$this->getDialect(),
		);
	}

	/**
	 * @param class-string $className
	 * @param string[] $fields
	 * @param string[] $metaFields
	 */
	public function createLateUpdate(string $className, array $fields, array $metaFields): BulkUpdate
	{
		$metadata = $this->em->getClassMetadata($className);

		return new BulkUpdate(
			$this->em,
			new BulkLateData(
				$metadata,
				$this->createColumnFieldArray($fields, $metadata),
				$this->createColumnFieldArray($metaFields, $metadata),
			),
			$this->getDialect(),
		);
	}

	/**
	 * @param class-string $className
	 * @param string[] $fields
	 */
	public function createInsert(string $className, array $fields): BulkInsert
	{
		$metadata = $this->em->getClassMetadata($className);

		return new BulkInsert(
			$this->em,
			new BulkInstantData($metadata, $this->createColumnFieldArray($fields, $metadata)),
			$this->getDialect(),
		);
	}

	/**
	 * @param class-string $className
	 * @param string[] $fields
	 */
	public function createLateInsert(string $className, array $fields): BulkInsert
	{
		$metadata = $this->em->getClassMetadata($className);

		return new BulkInsert(
			$this->em,
			new BulkLateData($metadata, $this->createColumnFieldArray($fields, $metadata)),
			$this->getDialect(),
		);
	}

	private function getDialect(): Dialect
	{
		if (!isset($this->dialect)) {
			$platform = $this->em->getConnection()->getDatabasePlatform();

			if (!$platform instanceof MySQLPlatform) {
				throw new InvalidArgumentException(sprintf('Platform %s is not supported.', $platform::class));
			}

			$this->dialect = new MysqlDialect();
		}

		return $this->dialect;
	}

	/**
	 * @param string[] $fields
	 * @param ClassMetadata<object> $metadata
	 * @return array<string, string>
	 */
	private function createColumnFieldArray(array $fields, ClassMetadata $metadata): array
	{
		$return = [];

		foreach ($fields as $field) {
			if ($metadata->hasField($field)) {
				$return[$field] = $metadata->getColumnName($field);
			} else {
				try {
					$metadata->getSingleAssociationJoinColumnName($field);
				} catch (MappingException) {
					throw new InvalidArgumentException(sprintf('Field %s does not exist in %s.', $field, $metadata->getName()));
				}
			}
		}

		return $return;
	}

}
