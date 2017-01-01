---
layout: documentation
current_menu: container-configuration
---

# Configuring the container

## Development environment

PHP-DI's container is preconfigured for "plug'n'play", i.e. development environment. You can start using it simply like so:

```php
$container = ContainerBuilder::buildDevContainer();
// same as
$container = new Container();
```

By default, PHP-DI will have [Autowiring](definition.md) enabled (annotations are disabled by default).

## Production environment

In production environment, you will of course favor speed:

```php
$builder = new \DI\ContainerBuilder();
$builder->setDefinitionCache(new Doctrine\Common\Cache\ApcCache());
$builder->writeProxiesToFile(true, 'tmp/proxies');

$container = $builder->build();
```

To choose a cache, read [the performances documentation](performances.md).

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
$container = new CompositeContainer();

// Add Symfony's container
$container->addContainer($acclimate->adaptContainer($symfonyContainer));

// Configure PHP-DI container
$builder = new ContainerBuilder();
$builder->wrapContainer($container);

// Add PHP-DI container
$phpdiContainer = $builder->build();
$container->addContainer($acclimate->adaptContainer($phpdiContainer));

// Good to go!
$foo = $container->get('foo');
```

## Ignoring phpDoc errors

*Added in v4.4*

If you use annotations and your phpDoc is not always correct, you can set up the container to silently ignore those errors:

```php
$builder->ignorePhpDocErrors(true);
```

For example:

```php
class Foo
{
    /**
     * @param NonExistentClass $param
     */
    public function useAutowiring($param)
    {
    }
}
```

Here, PHP-DI will throw an exception because `NonExistentClass` doesn't exist: this is a phpDoc error.

There has been reports that PHP-FPM might choke on such errors and report it with a message like this:

> Handler for fastcgi-script returned invalid result code 1

In case the errors still occur, make sure your annotations are correct or temporarily disable annotations (`$builder->useAnnotations(false)`) to prevent fatal errors and try to clean up your configuration form there.
