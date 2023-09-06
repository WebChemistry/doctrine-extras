<?php declare(strict_types = 1);

namespace WebChemistry\DoctrineExtras\Identity;

interface EntityWithIdentity
{

	/**
	 * @return EntityIdentity
	 */
	public function identity(): EntityIdentity;

}
