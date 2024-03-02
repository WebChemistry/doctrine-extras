<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use WebChemistry\DoctrineExtras\Utility\DoctrineUtility;

final class EntityMapHelper
{

	public static function stringifyId(mixed $id, ?EntityManagerInterface $em = null): string
	{
		if (is_object($id)) {
			if (!$em) {
				return get_debug_type($id);
			}

			try {
				$metadata = $em->getClassMetadata($id::class);
			} catch (MappingException) {
				return get_debug_type($id);
			}

			return DoctrineUtility::getStringId($metadata, $id);
		}

		if (is_array($id)) {
			return serialize($id);
		}

		return (string) $id; // @phpstan-ignore-line
	}

	/**
	 * @param ClassMetadata<object> $metadata
	 */
	public static function stringifyIdByClassMetadata(ClassMetadata $metadata, mixed $id): ?string
	{
		if (is_object($id)) {
			return DoctrineUtility::tryGetStringId($metadata, $id);
		}

		if (is_array($id)) {
			return serialize($id);
		}

		return (string) $id; // @phpstan-ignore-line
	}

}
