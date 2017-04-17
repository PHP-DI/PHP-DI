---
layout: documentation
current_menu: performances
---

# Performances

## Cache

PHP-DI uses the [definitions](definition.md) you configured to instantiate classes.

Reading those definitions (and, if enabled, reading [autowiring](autowiring.md) or [annotations](annotations.md)) on each request can be avoided by using a cache.

PHP-DI is compatible with all [PSR-16](https://github.com/php-fig/simple-cache) caches (PSR-16 is a standard for PHP cache systems). To choose which library you want to use, you can have a look at [Packagist](https://packagist.org/providers/psr/simple-cache-implementation). You can for example use Symfony's [cache component](https://github.com/symfony/cache) or the [PHP Cache library](http://www.php-cache.com/en/latest/).

### Setup

There is no cache system installed by default with PHP-DI, you need to install it with Composer. The examples below use the Symfony cache component.

```
composer require symfony/cache
```

You can then pass a cache instance to the container builder:

```php
$cache = new Symfony\Component\Cache\Simple\ApcuCache();
$containerBuilder->setDefinitionCache($cache);
```

Heads up: do not use a cache in a development environment, else all the changes you make to the definitions (annotations, configuration files, etc.) may not be taken into account. The only cache you may use in development is `DI\Cache\ArrayCache` (which is the only cache implementation provided by PHP-DI) because it doesn't persist data between requests.

### Cache types

Depending on the cache library you will choose, it will provide adapters to different kind of backends, for example: APCu, Memcache, Redis, Filesystem, etc.

Here is the list of caches Symfony Cache supports: [supported adapters](http://symfony.com/doc/current/components/cache.html#available-simple-cache-psr-16-classes).

In production environments, **caches based on APCu are recommended**. Given PHP-DI's architecture, there will be a cache request for each container entry you get. Remote caches (like Redis or Memcache) will most certainly have a latency too high for such low level calls and will not be efficient.

### Cache prefixes

If you run the same application twice on the same machine, both installs will use the same cache which can cause conflicts.

Conflicts can also happen if an application runs on different "environments" (e.g. production, development…) on the same machine (see the [environments documentation](environments.md)).

To avoid this situation, you should use a cache "prefix": each installation of your app has a unique ID, and this ID is used to prefix cache keys
to avoid collisions.

You can also add your application's version to the cache prefix so that on each new deployment the cache for the old version is discarded.

For example with Symfony Cache:

```php
$environment = 'prod'; // or 'dev'
$appVersion = '...';
$namespace = 'MySuperApplication-' . $environment . '-' . $appVersion;

$cache = new Symfony\Component\Cache\Simple\ApcuCache($namespace);
$containerBuilder->setDefinitionCache($cache);
```
