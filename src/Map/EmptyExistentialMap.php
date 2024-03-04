<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

/**
 * @template TEntity of object
 * @extends BaseEmptyEntityMap<TEntity, false>
 */
final class EmptyExistentialMap extends BaseEmptyEntityMap
{

	public function get(object|array|int|string|null $id): mixed
	{
		return false;
	}

}
