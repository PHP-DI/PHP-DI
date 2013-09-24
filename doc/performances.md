# Performances

## Cache

PHP-DI uses the [definitions](definition.md) you configured to instantiate classes.

Getting and parsing those definitions on each request is useless, so to improve performances you can cache these definitions.

PHP-DI offers an easy and complete solution to put those data into a cache based on Doctrine caches (and it is recommended to use it).


### Setup

```php
$container->setDefinitionCache(new Doctrine\Common\Cache\ApcCache());
```

Heads up: do not use a cache in a development environment, else changes you make to the definitions
(annotations, configuration files, etc.) may not be taken into account.
The only cache you should use in development is the `ArrayCache` because it doesn't persist data between requests.
Of course, do not use this one in production.

### Cache types

The cache implementation is provided by Doctrine (because it works very well) and contains:

- ApcCache (requires ext/apc)
- ArrayCache (in memory, lifetime of the request)
- FilesystemCache (not optimal for high concurrency)
- MemcacheCache (requires ext/memcache)
- MemcachedCache (requires ext/memcached)
- PhpFileCache (not optimal for high concurrency)
- RedisCache.php (requires ext/phpredis)
- WinCacheCache.php (requires ext/wincache)
- XcacheCache.php (requires ext/xcache)
- ZendDataCache.php (requires Zend Server Platform)

Read the [Doctrine documentation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/caching.html)
for more details.

### Cache prefixes

If you run the same application twice on the same machine, both installs will use the same cache, and there might be conflicts.

To avoid this situation, you should use a cache "prefix": each installation of your app has a unique ID, and this ID is used to prefix cache keys
to avoid collisions.

```php
$cache = new Doctrine\Common\Cache\ApcCache();
$cache->setNamespace('MyApplication');
$container->setDefinitionCache($cache);
```
