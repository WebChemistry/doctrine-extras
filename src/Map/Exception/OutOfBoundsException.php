<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map\Exception;

use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Map\EntityMapHelper;

final class OutOfBoundsException extends \OutOfBoundsException
{

	public static function notExists(mixed $id, ?EntityManagerInterface $em = null): self
	{
		return new self(sprintf('Identity %s does not exist in map.', EntityMapHelper::stringifyId($id, $em)));
	}

}
