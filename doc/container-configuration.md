---
layout: documentation
current_menu: container-configuration
---

# Configuring the container

## Development environment

PHP-DI's container is preconfigured for "plug and play", i.e. development environment. You can start using it simply like so:

```php
$container = new Container();
```

By default, PHP-DI will have [Autowiring](definition.md) enabled ([annotations](annotations.md) are disabled by default).

To change options on the container you can use the `ContainerBuilder` class:

```php
$builder = new \DI\ContainerBuilder();
$container = $builder->build();
```

## Production environment

In production environment, you will of course favor speed:

```php
$builder = new \DI\ContainerBuilder();
$builder->enableCompilation(__DIR__ . '/tmp');
$builder->writeProxiesToFile(true, __DIR__ . '/tmp/proxies');

$container = $builder->build();
```

Read [the performances documentation](performances.md) to learn more.

## Lightweight container

If you want to use PHP-DI's container as a simple container (no autowiring or annotation support), you will want to disable all extra features.

```php
$builder = new \DI\ContainerBuilder();
$builder->useAutowiring(false);
$builder->useAnnotations(false);

$container = $builder->build();
```

Note that this doesn't necessarily means that the container will be faster, since everything can be cached anyway.
Read more about this in [the performances documentation](performances.md).

## Using PHP-DI with other containers

If you want to use several containers at once, for example to use PHP-DI in ZF2 or Symfony 2, you can
use a tool like [Acclimate](https://github.com/jeremeamia/acclimate).

You will just need to tell PHP-DI to look into the composite container, else PHP-DI will be unaware
of Symfony's container entries.

Example with Acclimate:

```php
$container = new Acclimate\Container\CompositeContainer();

// Add Symfony's container
$container->addContainer($acclimate->adaptContainer($symfonyContainer));

// Configure PHP-DI container
$builder = new \DI\ContainerBuilder();
$builder->wrapContainer($container);

// Add PHP-DI container
$phpdiContainer = $builder->build();
$container->addContainer($phpdiContainer);

// Good to go!
$foo = $container->get('foo');
```
