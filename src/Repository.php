<?php namespace Inkwell\Doctrine
{
	use Doctrine\ORM\EntityRepository;

	/**
	 * Base Repository Class
	 *
	 */
	class Repository extends EntityRepository
	{
		/**
		 *
		 */
		public function create()
		{
			return new $this->getEntityName();
		}
	}
}
