<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Utility;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use LogicException;
use Nette\NotSupportedException;
use Stringable;

final class DoctrineUtility
{

	/** @var array<string, string> */
	private static $singleIdentifier = [];

	/**
	 * @param ClassMetadata<object> $metadata
	 */
	public static function isEntityEqualToMetadata(object $entity, ClassMetadata $metadata): bool
	{
		$className = $entity::class;
		$metadataName = $metadata->getName();

		return $metadataName === $className || ClassUtils::getRealClass($className) === $metadataName;
	}

	/**
	 * @param ClassMetadata<object> $metadata
	 */
	public static function tryGetStringId(ClassMetadata $metadata, object $entity): ?string
	{
		if (!self::isEntityEqualToMetadata($entity, $metadata)) {
			return null;
		}

		return self::getStringId($metadata, $entity);
	}

	/**
	 * @template T of object
	 * @param ClassMetadata<T> $metadata
	 * @param T $entity
	 */
	public static function getStringId(ClassMetadata $metadata, object $entity): string
	{
		if ($metadata->isIdentifierComposite) {
			return serialize($metadata->getIdentifierValues($entity));
		}

		$value = $metadata->getFieldValue(
			$entity,
			self::$singleIdentifier[$metadata->getName()] ??= $metadata->getSingleIdentifierFieldName(),
		);

		if (is_scalar($value) || $value === null || $value instanceof Stringable) {
			return (string) $value;
		}

		throw new LogicException(sprintf(
			'Entity %s has non-scalar and non-stringable identifier, which is not supported.',
			$metadata->getName(),
		));
	}

	public static function getSingleIdValue(EntityManagerInterface $em, object $object): string|int
	{
		$metadata = $em->getClassMetadata($object::class);

		$values = $metadata->getIdentifierValues($object);

		if (count($values) !== 1) {
			throw new NotSupportedException(
				sprintf('Entity %s has more than one identifier, which is not supported yet.', $object::class)
			);
		}

		$id = reset($values);

		if ($id instanceof Stringable) {
			$id = (string) $id;
		}

		if (!is_string($id) && !is_int($id)) {
			throw new LogicException(sprintf('Entity %s does not have string or int id, %s given.', $object::class, get_debug_type($id)));
		}

		return $id;
	}

}
