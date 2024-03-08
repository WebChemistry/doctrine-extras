<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Map;

/**
 * @template TEntity of object
 * @extends BaseEmptyEntityMap<TEntity, bool>
 * @implements ExistentialMap<TEntity>
 */
final class EmptyExistentialMap extends BaseEmptyEntityMap implements ExistentialMap
{

	public function get(object|array|int|string|null $id): mixed
	{
		return false;
	}

}
