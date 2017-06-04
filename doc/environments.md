---
layout: documentation
current_menu: environments
---

# Injections depending on the environment

You may want to inject different parameters or objects according to the environment, for example:

```php
<?php
return [
    // These values change according to the environment
    'db.host' => 'localhost',
    'db.port' => 3336,

    'DbAdapter' => DI\create()
        ->constructor(DI\get('db.host'), DI\get('db.port')),
];
```

To achieve this, you can create a main `config.php` file and have small configuration files for each environment:

```php
<?php
// config.prod.php
return [
    'db.host' => '178.231.21.29',
    'db.port' => 5000,
];
```

```php
<?php
// config.dev.php
return [
    'db.host' => 'localhost',
    'db.port' => 3336,
];
```

```php
<?php
// config.php
return [
    'DbAdapter' => DI\create()
        ->constructor(DI\get('db.host'), DI\get('db.port')),
];
```

Then you can configure your container to including the correct files:

```php
$builder = new ContainerBuilder();

// Main configuration
$builder->addDefinitions("config.php");

// Config file for the environment
$builder->addDefinitions("config.$environment.php");

$container = $builder->build();
```

## Compilation

If you configure PHP-DI to be compiled you need to compile each environment into a separate file to avoid mixups.

Read the [Performances](performances.md) documentation to learn about compiling the container.
