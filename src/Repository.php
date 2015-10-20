<?php namespace Inkwell\Doctrine
{
	use Dotink\Flourish;
	use Doctrine\ORM\EntityManager;
	use Doctrine\ORM\EntityRepository;

	/**
	 * Base Repository Class
	 *
	 */
	class Repository extends EntityRepository
	{
		const MODEL = NULL;


		/**
		 *
		 */
		static public $defaultOrder = array();


		/**
		 *
		 */
		public function __construct(EntityManager $entity_manager)
		{
			if (!static::MODEL) {
				throw new Flourish\ProgrammerException('Must set model on repository class');
			}

			$this->model = static::MODEL;

			parent::__construct($entity_manager, $entity_manager->getclassMetaData($this->model));
		}


		/**
		 *
		 */
		public function create()
		{
			return new $this->model();
		}


		/**
		 *
		 */
		public function save($entity, $flush = TRUE)
		{
			if (!($entity instanceof $this->model)) {
				throw new Flourish\ProgrammerException();
			}

			$this->_em->persist($entity);

			if ($flush) {
				$this->_em->flush();
			}
		}
	}
}
