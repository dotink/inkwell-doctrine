<?php

	return Affinity\Config::create(['quill', 'providers', 'middleware'], [
		'@quill' => [
			'commands' => [
				'Inkwell\Doctrine\Command\OrmGenerateClassesCommand'
			]
		],

		'@providers' => [
			'mapping' => [
				'Doctrine\Common\Persistence\ObjectManager' => 'Doctrine\ORM\EntityManager',
			]
		],

		'@middleware' => [
			'providers' => [
				'Inkwell\Doctrine\Middleware\Flush'
			]
		]
	]);
