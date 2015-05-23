---
layout: documentation
---

# Injections depending on the environment

You may want to inject different parameters or objects according to the environment, for example:

```php
<?php
return [
    // These values change according to the environment
    'db.host' => 'localhost',
    'db.port' => 3336,

    'DbAdapter' => DI\object()
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
    'DbAdapter' => DI\object()
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

## Caches

If you configure PHP-DI to use a cache, you need to make sure that different environments don't share the same cache. To solve that problem, it is recommended to use a **cache prefix**: read about this in the [Performances](performances.md) documentation.
