<?php

	return Affinity\Config::create([

		//
		// A NULL type will indicate that no database is configure. Common drivers include
		// `pdo_mysql`, `pdo_pgsql`, `pdo_sqlite`, for a complete list see:
		// http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver
		//

		'driver' => $app->getEnvironment('DB_TYPE', NULL),

		//
		// You should set these in your environment so that they can change more easily if the app
		// is deployed in many places or with different settings.
		//

		'host'     => $app->getEnvironment('DB_HOST', 'localhost'),
		'dbname'   => $app->getEnvironment('DB_NAME', NULL),
		'user'     => $app->getEnvironment('DB_USER', NULL),
		'password' => $app->getEnvironment('DB_PASS', NULL),
	]);
