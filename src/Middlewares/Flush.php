<?php namespace Inkwell\Doctrine\Middleware
{
	use Inkwell\Http\Resource\Request;
	use Inkwell\Http\Resource\Response;

	use Doctrine\ORM\EntityManager;

	/**
	 * A simple middleware to flush the database on the way out
	 */
	class Flush
	{
		/**
		 * The entity manager
		 *
		 * @access protected
		 * @var
		 */
		protected $entityManager = NULL;

		/**
		 * Instantiate a new Flush middleware
		 *
		 * @access public
		 * @param EntityManager $entity_manager The entity manager to flush
		 * @return void
		 */
		public function __construct(EntityManager $entity_manager)
		{
			$this->entityManager = $entity_manager;
		}


		/**
		 * Flush the entity manager on the way out
		 *
		 * @access public
		 * @param Request $request The request in its current incoming state
		 * @param Response $response The response in its current incoming state
		 * @return Response $response The response in its modified state
		 */
		public function __invoke(Request $request, Response $response, $next = NULL)
		{
			$response = $next($request, $response);

			$this->entityManager->flush();

			return $response;
		}
	}
}
