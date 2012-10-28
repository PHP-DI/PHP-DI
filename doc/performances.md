# Performances

## Annotation cache

PHP-DI reads the annotations using [Doctrine Annotations](http://docs.doctrine-project
.org/projects/doctrine-common/en/latest/reference/annotations.html).

This can be an expensive process, so it is highly suggested to use a cache for those annotations. Doctrine already provides a cache
system that can use files, [APC](http://www.php.net/manual/en/intro.apc.php) or [Memcached](http://memcached.org/).

Here is an example of how you can set up a cache for PHP-DI:

```php
<?php
use DI\Container;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;

$reader = new CachedReader(
    new AnnotationReader(),
    new ApcCache(),
    $debug = true
);

$container = Container::getInstance();
$container->setAnnotationReader($reader);
```

Read the [official Doctrine documentation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations
.html#setup-and-configuration) to see how you can configure the cache.
