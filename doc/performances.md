# Performances

## Cache

PHP-DI uses the [definitions](definition.md) you configured to instantiate classes.

Getting and parsing those definitions on each request is useless, so to improve performances you can cache these definitions.

PHP-DI offers an easy and complete solution to put those data into a cache based on Doctrine caches (and it is recommended to use it).


### Setup

```php
$container->setDefinitionCache(new Doctrine\Common\Cache\ArrayCache());
```

Heads up: It is recommended not to use a cache in a development environment, else changes you make to the definitions (annotations, configuration files, etc.) may not be taken into account.


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
