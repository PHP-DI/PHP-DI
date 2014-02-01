---
template: documentation
---

# Injections depending on the environment

You may want to inject different parameters according to the environment, for example:

```php
<?php
return [
    // These values change according to the environment
    'db.host' => 'localhost',
    'db.port' => 3336,

    'DbAdapter' => DI\object()
        ->constructor(DI\link('db.host'), DI\link('db.port')),
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
        ->constructor(DI\link('db.host'), DI\link('db.port')),
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
