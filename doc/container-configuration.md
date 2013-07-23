# Configuring the container

## Development environment

PHP-DI's container is preconfigured for "plug'n'play", i.e. development environment. You can start using it simply like so:


```php
$container = new \DI\Container();
```

By default, PHP-DI will use `Reflection` and `Annotation` [definition sources](definition.md).

To improve performances, you may want to use a simple Array cache. This kind of cache is cleared on every request, so you will not need to empty it manually when you change the code. It is also recommended to enable definition validation to detect errors as soon as possible.

```php
$builder = new \DI\ContainerBuilder();
$builder->setDefinitionCache(new Doctrine\Common\Cache\ArrayCache());
$builder->setDefinitionsValidation(true);

$container = $builder->build();
```

### Adding a definition file

If you defining injections in a file, use the container builder:

```php
use DI\Definition\FileLoader\YamlDefinitionFileLoader;

$builder->addDefinitionsFromFile(new YamlDefinitionFileLoader('config/di.yml'));
```

If you don't want to use Reflection or Annotations, disable them:

```php
$builder->useReflection(false);
$builder->useAnnotations(false);
```

Read more about [definitions](definition.md).

## Production environment

In production environment, you will of course favor speed:

```php
$builder = new \DI\ContainerBuilder();
$builder->setDefinitionCache($cache);
$builder->writeProxiesToFile(true, 'tmp/proxies');
$builder->setDefinitionsValidation(false);

$container = $builder->build();
```

To choose a cache, read [the performances documentation](performances.md).

## Lightweight container

If you want to use PHP-DI's container as a simple container, you will want to disable all extra features.

```php
$builder = new \DI\ContainerBuilder();
$builder->useReflection(false);
$builder->useAnnotations(false);
$builder->setDefinitionsValidation(false);

$container = $builder->build();
```
