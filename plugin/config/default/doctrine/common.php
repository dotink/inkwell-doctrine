<?php

	return Affinity\Config::create(['quill'], [
		'@quill' => [
			'commands' => [
				'Inkwell\Doctrine\Command\OrmGenerateClassesCommand'
			]
		]
	]);
