<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map\Exception;

use Exception;

final class NotSupportedException extends Exception
{

	public function __construct(string $operation)
	{
		parent::__construct(sprintf('Operation %s is not supported.', $operation));
	}

}
