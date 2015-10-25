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
		//
		// The default is yaml, although both yaml and xml should work with entity generation
		//

		'config_type' => 'yaml',

		// For purposes of code generation, base classes are created and can extend an initial
		// root class.

		'root_class' => NULL,

		// For purposes of code generation, base classes are created and then extended, this
		// namespace will be spliced into the namespace such that Example\Whatever will become
		// Example\Base\Whatever

		'base_namespace' => 'Base',

		//
		// The `entity_root` and `config_root` will be relative to your inKWell application
		// root unless you specify an absolute path (preceded with '/').
		//

		'entity_root' => 'user/entities',

		'config_root' => 'config/default/doctrine/entities',

		'repository_root' => 'user/repositories'
	]);
