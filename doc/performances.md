---
layout: documentation
---

# Performances

## Cache

PHP-DI uses the [definitions](definition.md) you configured to instantiate classes.

Reading those definitions (and, if enabled, reading annotations or autowiring) on each request can be avoided by using a cache. The caching system PHP-DI uses is the Doctrine Cache library.

### Setup

```php
$containerBuilder->setDefinitionCache(new Doctrine\Common\Cache\ApcCache());
```

Heads up: do not use a cache in a development environment, else changes you make to the definitions (annotations, configuration files, etc.) may not be taken into account.
The only cache you might use in development is the `ArrayCache` because it doesn't persist data between requests.

### Cache types

The cache implementation is provided by Doctrine (because it works very well) and contains:

- `ArrayCache` (in memory, lifetime of the request)
- `ApcCache` (requires the APC or APCu extension)
- `FilesystemCache` (not optimal for high concurrency)
- `MemcacheCache` (requires the memcache extension)
- `MemcachedCache` (requires the memcached extension)
- `PhpFileCache` (not optimal for high concurrency)
- `RedisCache` (requires the phpredis extension)
- `WinCacheCache` (requires the wincache extension)
- `XcacheCache` (requires the xcache extension)
- `ZendDataCache` (requires Zend Server Platform)

Read the [Doctrine documentation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/caching.html) for more details.

The recommended cache is the `ApcCache`.

### Cache prefixes

If you run the same application twice on the same machine, both installs will use the same cache, and there might be conflicts.

To avoid this situation, you should use a cache "prefix": each installation of your app has a unique ID, and this ID is used to prefix cache keys
to avoid collisions.

```php
$cache = new Doctrine\Common\Cache\ApcCache();
$cache->setNamespace('MyApplication');
$containerBuilder->setDefinitionCache($cache);
```

You are also encouraged to use cache prefixes if your application can run in several different environments (for example "development" and "production"): you don't want to share the cache between environments.
