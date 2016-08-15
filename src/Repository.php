<?php namespace Inkwell\Doctrine
{
	use Dotink\Flourish;
	use Tenet\Accessor;
	use Doctrine\ORM\EntityManager;
	use Doctrine\ORM\EntityRepository;
	use Doctrine\ORM\Tools\Pagination\Paginator;
	use Doctrine\Common\Collections\ArrayCollection;
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
			$metadata_factory = $entity_manager->getMetaDataFactory();

			foreach ($metadata_factory->getAllMetaData() as $class => $metadata) {
				if ($metadata->customRepositoryClassName == get_class($this)) {
					$this->model = $metadata->getName();
				}
			}

			parent::__construct($entity_manager, $entity_manager->getclassMetaData($this->model));
		}


		/**
		 *
		 */
		public function build($builder, $limit = NULL, $page = 1)
		{
			$query = $this->query($builder);

			$query->setFirstResult(($page - 1) * $limit);

			if ($limit) {
				$query->setMaxResults($limit);
			}

			return new Paginator($query, $fetchJoinCollection = true);
		}


		/**
		 *
		 */
		public function create()
		{
			return new $this->model();
		}


		/**
		 * Finds entities by a set of criteria.
		 *
		 * @param array      $criteria
		 * @param array|null $orderBy
		 * @param int|null   $limit
		 * @param int|null   $offset
		 *
		 * @return array The objects.
		 */
		public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
		{
			$persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);

			return new ArrayCollection($persister->loadAll($criteria, $orderBy, $limit, $offset));
		}


		/**
		 *
		 */
		public function isInstance($entity)
		{
			return $entity instanceof $this->model;
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
		public function query($builder)
		{
			$query = $this->_em
				-> createQueryBuilder()
				-> select('data')
				-> from($this->model, 'data')
			;

			if (is_callable($builder)) {
				$builder($query);

			} elseif (is_string($builder) || is_array($builder)) {
				settype($builder, 'array');

				foreach ($builder as $method) {
					if (!is_callable($method)) {
						$method = [$this, 'query' . ucfirst($method)];
					}

					$method($query);
				}

			} else {
				throw new Flourish\ProgrammerException('Invalid builder type');
			}

			return $query->getQuery();
		}


		/**
		 *
		 */
		public function remove($entity, $flush = FALSE)
		{
			if (!$this->isInstance($entity)) {
				throw new Flourish\ProgrammerException(
					'Cannot remove entity of type "%s", must be a "%s"',
					get_class($entity),
					$this->model
				);
			}

			$this->_em->remove($entity);

			if ($flush) {
				$this->_em->flush();
			}
		}


		/**
		 *
		 */
		public function save($entity, $flush = FALSE)
		{
			if (!$this->isInstance($entity)) {
				throw new Flourish\ProgrammerException(
					'Cannot save entity of type "%s", must be a "%s"',
					get_class($entity),
					$this->model
				);
			}

			$this->_em->persist($entity);

			if ($flush) {
				$this->_em->flush();
			}
		}
	}
}
