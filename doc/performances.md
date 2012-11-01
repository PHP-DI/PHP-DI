# Performances

## Cache

In order to work, PHP-DI has to parse your code to find annotations. This parsing is based on [Doctrine Annotations](http://docs.doctrine-project
.org/projects/doctrine-common/en/latest/reference/annotations.html).

Like Doctrine does, PHP-DI offers a simple solution to put those data into a cache (and it is recommended to use it).

### Setup

```php
<?php
use DI\Container;
use DI\MetadataReader\DefaultMetadataReader;
use DI\MetadataReader\CachedMetadataReader;

$metadataReader = new CachedMetadataReader(
	new DefaultMetadataReader(),
	new Doctrine\Common\Cache\ArrayCache(),
	$debug = true
)

$container = Container::getInstance();
$container->setMetadataReader($metadataReader);
```

The debug flag is used to invalidate the cache files when your code changed. This flag should be used during development (this is the
same flag than in [Doctrine annotation setup](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations
.html#setup-and-configuration)).

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

Read the [Doctrine documentation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/caching.html) for more
details.
