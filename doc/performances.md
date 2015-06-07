---
layout: documentation
---

# Performances

## Cache

PHP-DI uses the [definitions](definition.md) you configured to instantiate classes.

Reading those definitions (and, if enabled, reading annotations or autowiring) on each request can be avoided by using a cache. The caching system PHP-DI uses is the [Doctrine Cache](http://doctrine-common.readthedocs.org/en/latest/reference/caching.html) library.

### Setup

The Doctrine Cache library is not installed by default with PHP-DI, you need to install it with Composer:

```json
{
    "require": {
        ...
        "doctrine/cache": "~1.0"
    }
}
```

Then you can then pass a cache instance to the container builder:

```php
$containerBuilder->setDefinitionCache(new Doctrine\Common\Cache\ApcCache());
```

Heads up: do not use a cache in a development environment, else changes you make to the definitions (annotations, configuration files, etc.) may not be taken into account. The only cache you might use in development is the `ArrayCache` because it doesn't persist data between requests.

### Cache types

The cache implementation is provided by Doctrine (because it works very well) and contains:

- `ArrayCache` (in memory, lifetime of the request)
- `ApcCache` (requires the APC or APCu extension)
- `MemcacheCache` (requires the memcache extension)
- `MemcachedCache` (requires the memcached extension)
- `RedisCache` (requires the phpredis extension)
- `FilesystemCache` (not optimal for high concurrency)
- `PhpFileCache` (not optimal for high concurrency)
- `WinCacheCache` (requires the wincache extension)
- `XcacheCache` (requires the xcache extension)
- `ZendDataCache` (requires Zend Server Platform)

Read the [Doctrine documentation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/caching.html) for more details.

The recommended cache is the `ApcCache`.

### Cache prefixes

If you run the same application twice on the same machine, both installs will use the same cache which can cause conflicts.

Conflicts can also happen if an application runs on different "environments" (e.g. production, development…) on the same machine (see the [environments documentation](environments.md)).

To avoid this situation, you should use a cache "prefix": each installation of your app has a unique ID, and this ID is used to prefix cache keys
to avoid collisions.

```php
$cache = new Doctrine\Common\Cache\ApcCache();
$cache->setNamespace('MyApplication');
$containerBuilder->setDefinitionCache($cache);
```
