<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Index;

use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DoctrineExtras\Utility\DoctrineUtility;

final class EntityIndexFactory
{

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	/**
	 * @template T of object
	 * @param T[] $entities
	 * @return EntityIndex<T>
	 */
	public function create(array $entities): EntityIndex
	{
		if (!$entities) {
			return new EntityIndex();
		}

		$index = [];

		foreach ($entities as $entity) {
			$index[DoctrineUtility::getSingleIdValue($this->em, $entity)] = $entity;
		}

		return new EntityIndex($index);
	}

}
