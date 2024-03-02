<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map\Exception;

use Exception;

final class NotSupportedException extends Exception
{

	public static function operation(string $operation): self
	{
		return new self(sprintf('Operation %s is not supported.', $operation));
	}

}
