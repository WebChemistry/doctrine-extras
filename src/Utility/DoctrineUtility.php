<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Utility;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Nette\NotSupportedException;
use Stringable;

final class DoctrineUtility
{

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
