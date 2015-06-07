---
layout: documentation
---

# PHP-DI in Silex

## Installation

```
$ composer require php-di/silex-bridge
```

## Usage

In order to benefit from PHP-DI's integration in Silex, you only need to use `DI\Bridge\Silex\Application` instead of the original `Silex\Application`.

Here is the classic Silex example updated:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new DI\Bridge\Silex\Application();

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
});

$app->run();
```

## Benefits

Using PHP-DI in Silex allows you to use all the awesome features of PHP-DI to wire your dependencies (using the definition files, autowiring, annotations, …).

Another big benefit of the PHP-DI integration is the ability to use dependency injection inside controllers:

```php
class Mailer
{
    // ...
}

$app->post('/register/{name}', function ($name, Mailer $mailer) {
    $mailer->sendMail($name, 'Welcome!');

    return 'You have received a new email';
});
```

Dependency injection in controllers works using type-hinting:

- it can be mixed with request parameters (`$name` in the example above)
- the order of parameters doesn't matter, they are resolved by type-hint (for dependency injection) and by name (for request parameters)
- it only works with objects that you can type-hint: you can't inject string/int values for example, and you can't inject container entries whose name is not a class/interface name (e.g. `twig` or `doctrine.entity_manager`)

## Configuring the container

You can configure PHP-DI's container by creating your own `ContainerBuilder` and passing it to the application:

```php
$containerBuilder = new DI\ContainerBuilder();

// E.g. setup a cache
$containerBuilder->setDefinitionCache(new ApcCache());

// Add definitions
$containerBuilder->addDefinitions([
    // place your definitions here
]);

// Register a definition file
$containerBuilder->addDefinitions('config.php');

$app = new DI\Bridge\Silex\Application($containerBuilder);
```

## Silex service providers

Silex offers several "service providers" to pre-configure some 3rd party libraries, for example Twig or Doctrine. You can still use those service providers with this integration (even though in bigger projects you might want to configure everything yourself).

Here is the example of the [TwigServiceProvider](http://silex.sensiolabs.org/doc/providers/twig.html):

```php
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/views',
]);

$app->get('/', function () use ($app) {
    return $app['twig']->render('home.twig');
});
```

Since Twig services are registered using a custom name instead of the actual class name (e.g. `twig` instead of the `Twig_Environment` class name), you cannot inject such dependencies into closures. If you want to inject in controller closures, you can alias entries with PHP-DI:

```php
$builder = new ContainerBuilder();

$builder->addDefinitions([
    'Twig_Environment' => \DI\get('twig'), // alias
]);

// ...

// Twig can now be injected in closures:
$app->post('/', function (Twig_Environment $twig) {
    return $twig->render('home.twig');
});
```

## More

Read more on the [Silex-Bridge project on Github](https://github.com/PHP-DI/Silex-Bridge).
