<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Bulk;

use Doctrine\DBAL\ParameterType;

final class BulkRow
{

	/**
	 * @param array<string, string> $data placeholder => column
	 * @param array{string, scalar|null, int}[] $dataParameters
	 * @param array<string, string> $meta placeholder => column
	 * @param array{string, scalar|null, int}[] $metaParameters
	 */
	public function __construct(
		public readonly array $data,
		public readonly array $dataParameters,
		public readonly array $meta = [],
		public readonly array $metaParameters = [],
	)
	{
	}

}
