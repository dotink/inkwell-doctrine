A Doctrine 2 ORM Package for inKWell
=====

This package sets up a doctrine 2 entity manager via inKWell configuration.
Note that at the moment there is no integration for CLI tools, but that is
planned for a future release.

## Installation

```
composer require dotink/inkwell-doctrine
```

## Configuration and Setup

You can configure your entity configuration options in
`config/default/doctrine/entities.php`.  If you're using the annotation
driver you will want to make sure you create the configured
`entity_root`.  The XML and YAML drivers will use the `config_root`.

Example (annotations):

```php
'config_type' => 'annotations',
'entity_root' => 'user/entities',
```

```bash
mkdir -p <project_root>/user/entities
```

Example (yaml):

```php
'config_type' => 'yaml',
'config_root' => 'config/doctrine/entities'
```

```bash
mkdir -p <project_root>/config/doctrine/entities
```

## Usage

The entity manager can be auto injected into controllers which use
the official [inkwell-controller](https://github.com/dotink/inkwell-controller)
package as follows:

```php
<?php

	use Inkwell\Controller\BaseController;
	use Doctrine\ORM\EntityManager;


	Controller extends BaseController
	{
		public function __construct(EntityManager $em)
		{
			$this->em = $em;
		}
	}
```
