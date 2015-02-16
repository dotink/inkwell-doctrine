<?php

	use Dotink\Flourish;
	use Doctrine\ORM\Tools\Setup;

	return Affinity\Action::create(['core'], function($app, $broker) {
		$dev_mode = $app->checkExecutionMode(IW\EXEC_MODE\DEVELOPMENT);

		extract($app['engine']->fetch('doctrine/entities', [
			'config_type' => 'annotations',
			'entity_root' => 'user/entities',
			'config_root' => 'config/doctrine/entities'
		]));

		switch ($config_type) {
			case 'annotations':
				$root   = $app->getDirectory($entity_root);
				$config = Setup::createAnnotationMetadataConfiguration([$root], $dev_mode);
				break;

			case 'yaml':
				$root   = $app->getDirectory($config_root);
				$config = Setup::createXMLMetadataConfiguration([$root], $dev_mode);
				break;

			case 'xml':
				$root   = $app->getDirectory($config_root);
				$config = Setup::createYAMLMetadataConfiguration([$root], $dev_mode);
				break;

			default:
				throw new Flourish\ProgrammerException(
					'Unsupported doctrine configuration type "%s"',
					$config_type
				);
		}

		$app['entity.config'] = $config;
	});
