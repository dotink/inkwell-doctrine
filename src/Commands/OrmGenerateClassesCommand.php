<?php namespace Inkwell\Doctrine\Command
{
	use Inkwell\Core as App;

	use Doctrine\DBAL\Types\Type;
	use Doctrine\ORM\EntityManager;
	use Doctrine\ORM\Mapping\ClassMetadataInfo;
	use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;

	use Psy\Command\Command;

	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;

	use Nette\PhpGenerator\ClassType;
	use Nette\PhpGenerator\PhpNamespace;

	class OrmGenerateClassesCommand extends Command
	{
		/**
		 *
		 */
		protected $toOneTypes = [
			ClassMetadataInfo::ONE_TO_ONE,
			ClassMetadataInfo::MANY_TO_ONE
		];

		/**
		 *
		 */
		protected $typeMap = [
			Type::DATETIMETZ    => '\DateTime',
			Type::DATETIME      => '\DateTime',
			Type::DATE          => '\DateTime',
			Type::TIME          => '\DateTime',
			Type::OBJECT        => '\stdClass',
			Type::BIGINT        => 'integer',
			Type::SMALLINT      => 'integer',
			Type::TEXT          => 'string',
			Type::BLOB          => 'string',
			Type::DECIMAL       => 'string',
			Type::JSON_ARRAY    => 'array',
			Type::SIMPLE_ARRAY  => 'array',
		];


		/**
		 *
		 */
		public function __construct(App $app, EntityManager $em, DisconnectedClassMetadataFactory $cmf)
		{
			$cmf->setEntityManager($em);

			$this->app           = $app;
			$this->metaData      = $cmf->getAllMetaData();
			$this->entityManager = $em;

			parent::__construct('orm:generate:classes');
		}


		/**
		 * {@inheritdoc}
		 */
		protected function configure()
		{
			$this
				->setDefinition(array())
				->setDescription('Generate base entity and repository classes')
				->setHelp(
<<<HELP
Generate base entity and repository classes

orm:generate:classes
HELP
				);
		}


		/**
		 * {@inheritdoc}
		 */
		protected function execute(InputInterface $input, OutputInterface $output)
		{
			$base_namespace = $this->app['engine']->fetch('doctrine/entities', 'base_namespace');

			$entity_root    = $this->app['engine']->fetch('doctrine/entities', 'entity_root');
			$entity_root    = $this->app->getDirectory($entity_root);

			$repo_root      = $this->app['engine']->fetch('doctrine/entities', 'repository_root');
			$repo_root      = $this->app->getDirectory($repo_root);


			foreach ($this->metaData as $meta_data) {
				$class_name = $meta_data->getName();
				$space_name = $this->parseNamespace($class_name);

				printf('Generated classes related to %s\%s', $space_name, $class_name);

				$base_space  = new PhpNamespace($space_name . '\\' . $base_namespace);
				$base_class  = $base_space->addClass($class_name);
				$constructor = $base_class->addMethod('__construct')
					-> setVisibility("public")
					-> addDocument("Instantiate a new " . $base_class->getName())
				;

				$base_space->setBracketedSyntax(TRUE);
				$base_space->addUse('Doctrine\Common\Collections\ArrayCollection');

				foreach ($meta_data->getFieldNames() as $field) {
					$type = $this->translateType($meta_data->getTypeOfField($field));

					$base_class->addProperty($field)
						-> setVisibility("protected")
						-> addDocument("")
						-> addDocument("@access protected")
						-> addDocument("@var $type");
					;

					$base_class->addMethod('get' . ucfirst($field))
						-> setVisibility("public")
						-> addDocument("Get the value of $field")
						-> addDocument("")
						-> addDocument("@access public")
						-> addDocument("@return $type The value of $field")
						-> addBody("return \$this->$field;")
					;


					$base_class->addMethod('set' . ucfirst($field))
						-> setVisibility("public")
						-> addDocument("Set the value of $field")
						-> addDocument("")
						-> addDocument("@access public")
						-> addDocument("@param $type \$value The value to set to $field")
						-> addDocument("@return " . $base_class->getName() . " The object instance for method chaining")
						-> addBody("\$this->$field = \$value;")
						-> addBody("")
						-> addBody("return \$this;")
						-> addParameter("value")
					;
				}

				foreach ($meta_data->getAssociationMappings() as $mapping) {
					$field = $mapping['fieldName'];
					$type  = in_array($mapping['type'], $this->toOneTypes)
						? $mapping['targetEntity']
						: 'ArrayCollection';

					$base_class->addProperty($field)
						-> setVisibility("protected")
						-> addDocument("")
						-> addDocument("@access protected")
						-> addDocument("@var $type");
					;

					$base_class->addMethod('get' . ucfirst($field))
						-> setVisibility("public")
						-> addDocument("Get the value of $field")
						-> addDocument("")
						-> addDocument("@access public")
						-> addDocument("@return $type The value of $field")
						-> addBody("return \$this->$field;")
					;

					if ($type == 'ArrayCollection') {
						$constructor->addBody("\$this->$field = new ArrayCollection();");
					}
				}

				$this->sortMethods($base_class);
				$this->write($entity_root, $base_space, $base_class, TRUE);

				$space = new PhpNamespace($space_name);
				$class = $space->addClass($class_name)
					-> setExtends($space_name . '\\' . $base_namespace . '\\' . $class_name)
				;

				$class->addMethod('__construct')
					-> setVisibility("public")
					-> addDocument("Instantiate a new " . $class->getName())
					-> addBody("return parent::__construct();");
				;

				$space->setBracketedSyntax(TRUE);

				$this->write($entity_root, $space, $class);

				if ($meta_data->customRepositoryClassName) {
					$repo_class_name = $meta_data->customRepositoryClassName;
					$repo_space_name = $this->parseNamespace($repo_class_name);

					$repo_space      = new PhpNamespace($repo_space_name);
					$repo_class      = $repo_space->addClass($repo_class_name);

					$repo_space->setBracketedSyntax(TRUE);
					$repo_space->addUse('Inkwell\Doctrine\Repository');
					$repo_class->setExtends('Inkwell\Doctrine\Repository');

					$this->write($repo_root, $repo_space, $repo_class);
				}

				echo PHP_EOL;
			}
		}


		/**
		 *
		 */
		protected function parseNamespace(&$class)
		{
			$parts = explode('\\', $class);
			$class = array_pop($parts);

			return implode('\\', $parts);
		}

		/**
		 *
		 */
		protected function sortMethods($class)
		{
			$methods = $class->getMethods();

			usort($methods, function($a, $b) {
				return $a->getName() < $b->getName()
					? -1
					: 1;
			});

			$class->setMethods($methods);
		}

		/**
		 *
		 */
		protected function translateType($type)
		{
			return isset($this->typeMap[$type])
				? $this->typeMap[$type]
				: $type;
		}


		/**
		 *
		 */
		protected function write($target_path, $space, $class, $overwrite = FALSE)
		{
			$space_path = str_replace('\\', DIRECTORY_SEPARATOR, $space->getName());
			$directory  = $target_path . DIRECTORY_SEPARATOR . $space_path;
			$file_path  = $directory . DIRECTORY_SEPARATOR . $class->getName() . '.php';

			if (file_exists($file_path) && !$overwrite) {
				return FALSE;
			}

			if (!is_dir($directory)) {
				if (!@mkdir($directory, 0755, TRUE)) {
					throw new Flourish\EnvironmentException(

					);
				}
			}

			return file_put_contents($file_path, '<?php ' . $space);
		}
	}
}
