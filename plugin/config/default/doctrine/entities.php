<?php

	return Affinity\Config::create([

		//
		// The configuration type determines how doctrine's entity configuration is done.
		// Possible values include:
		//
		// - annotations (config will be read from annotations on classes in your entity_root)
		// - xml         (config will be read from XML files in your config_root)
		// - yaml        (config will be read from YAML files in your config_root)
		//

		'config_type' => 'annotations',

		//
		// The `entity_root` and `config_root` will be relative to your inKWell application
		// root unless you specify an absolute path (preceded with '/').
		//

		'entity_root' => 'user/entities',

		'config_root' => 'config/default/doctrine/entities'

	]);
