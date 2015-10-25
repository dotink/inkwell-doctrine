<?php namespace Inkwell\Doctrine
{
	use Dotink\Flourish;
	use Doctrine\ORM\EntityManager;
	use Doctrine\ORM\EntityRepository;
	use Doctrine\ORM\UnitOfWork;

	/**
	 * A common repository on which to base others
	 *
	 */
	abstract class Repository extends EntityRepository
	{
		/**
		 * The default order for records when retrieved in bulk
		 *
		 * @static
		 * @access public
		 * @var array
		 */
		static public $defaultOrder = array();


		/**
		 *
		 */
		public function __construct(EntityManager $entity_manager)
		{
			$mdf = $entity_manager->getMetaDataFactory();

			foreach ($mdf->getAllMetaData() as $class => $metadata) {
				if ($metadata->customRepositoryClassName == get_class($this)) {
					$this->model = $metadata->getName();
				}
			}

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
		public function isPersisted($entity)
		{
			$uow = $this->_em->getUnitOfWork();

			return UnitOfWork::STATE_MANAGED == $uow->getEntityState($entity);
		}

		/**
		 *
		 */
		public function save($entity, $flush = FALSE)
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
