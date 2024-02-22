<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk\Blueprint;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;
use WebChemistry\DoctrineExtras\Bulk\Packet\BulkPacket;
use WebChemistry\DoctrineExtras\Bulk\Payload\BulkPayload;

/**
 * @template TEntity of object
 */
final class BulkBlueprint
{

	/** @var array<string, string> field => column */
	private array $ids = [];

	/** @var array<string, string> field => column */
	private array $fields = [];

	/** @var array<string, string> field => column */
	private array $columns = [];

	private int $columnCount;

	/** @var ClassMetadata<TEntity> */
	private ClassMetadata $classMetadata;

	/**
	 * @param class-string<TEntity> $className
	 * @param string[] $fields
	 */
	public function __construct(
		private string $className,
		private EntityManagerInterface $em,
		array $fields,
	)
	{
		$this->classMetadata = $this->em->getClassMetadata($this->className); // @phpstan-ignore-line
		$ids = $this->classMetadata->getIdentifierFieldNames();

		if (!$ids) {
			throw new InvalidArgumentException(sprintf('IDs is an empty array for %s.', $this->classMetadata));
		}

		foreach ($ids as $id) {
			$this->ids[$id] = $this->columns[$id] = $this->getColumnName($id);
		}

		foreach ($fields as $field) {
			if (isset($this->ids[$field])) {
				continue;
			}

			$this->fields[$field] = $this->columns[$field] = $this->getColumnName($field);
		}

		$this->columnCount = count($this->columns);
	}

	public function getFieldType(string $field): ?Type
	{
		$type = $this->classMetadata->getTypeOfField($field);

		if ($type !== null) {
			return Type::getType($type);
		}

		return null;
	}

	/**
	 * @return class-string<TEntity>
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

	public function getTableName(): string
	{
		return $this->classMetadata->getTableName();
	}

	/**
	 * @return string[]
	 */
	public function getColumnNames(): array
	{
		return array_values($this->columns);
	}

	/**
	 * @return string[]
	 */
	public function getColumnNamesForFields(): array
	{
		return array_values($this->fields);
	}

	/**
	 * @return string[]
	 */
	public function getColumnNamesForIds(): array
	{
		return array_values($this->ids);
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function createPacket(int $id, array $values): BulkPacket
	{
		$ids = [];
		$fields = [];

		if (count($values) !== $this->columnCount) {
			$messages = [];
			$missing = array_diff(array_keys($this->columns), array_keys($values));
			$unexpected = array_diff(array_keys($values), array_keys($this->columns));

			if ($missing) {
				$messages[] = sprintf('missing values for %s.', implode(', ', $missing));

			}

			if ($unexpected) {
				$messages[] = sprintf('unexpected values for %s.', implode(', ', $unexpected));
			}

			if (isset($messages[0])) {
				$messages[0] = ucfirst($messages[0]);
			}

			throw new InvalidArgumentException(implode(' ', $messages));
		}

		$platform = $this->em->getConnection()->getDatabasePlatform();

		foreach ($this->ids as $field => $column) {
			if (!array_key_exists($field, $values)) {
				throw new InvalidArgumentException(sprintf('Field %s is missing in values.', $field));
			}

			$type = $this->getFieldType($field);
			$value = $type ? $type->convertToDatabaseValue($values[$field], $platform) : $values[$field];

			if (!is_scalar($value) && $value !== null) {
				throw new InvalidArgumentException(sprintf('Field %s has to be scalar or null, %s given.', $field, get_debug_type($value)));
			}

			$ids[] = new BulkPayload($field, $column, $value);
		}

		foreach ($this->fields as $field => $column) {
			if (!array_key_exists($field, $values)) {
				throw new InvalidArgumentException(sprintf('Field %s is missing in values.', $field));
			}

			$type = $this->getFieldType($field);
			$value = $type ? $type->convertToDatabaseValue($values[$field], $platform) : $values[$field];

			if (!is_scalar($value) && $value !== null) {
				throw new InvalidArgumentException(sprintf('Field %s has to be scalar or null, %s given.', $field, get_debug_type($value)));
			}

			$fields[] = new BulkPayload($field, $column, $value);
		}

		return new BulkPacket($id, $ids, $fields);
	}

	private function getColumnName(string $field): string
	{
		if ($this->classMetadata->hasField($field)) {
			return $this->classMetadata->getFieldMapping($field)['columnName'];
		} else if (!$this->classMetadata->hasAssociation($field)) {
			throw new InvalidArgumentException(sprintf('Field %s does not exist in %s.', $field, $this->className));
		}

		return $this->classMetadata->getSingleAssociationJoinColumnName($field);
	}

}
