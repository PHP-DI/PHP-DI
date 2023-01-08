---
layout: documentation
current_menu: silex
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

Using PHP-DI in Silex allows you to use all the awesome features of PHP-DI to wire your dependencies (using the definition files, autowiring, attributes, …).

Another big benefit of the PHP-DI integration is the ability to use dependency injection inside controllers, middlewares and param converters:

```php
class Mailer
{
    // ...
}

$app->post('/register/{name}', function ($name, Mailer $mailer) {
    $mailer->sendMail($name, 'Welcome!');

    return 'You have received a new email';
});

// Injection works for middleware too
$app->before(function (Request $request, Mailer $mailer) {
    // ...
});

// And param converters
$app->get('/users/{user}', function (User $user) {
    return new JsonResponse($user);
})->convert('user', function ($user, UserManager $userManager) {
    return $userManager->findById($user);
});
```

Dependency injection works using type-hinting:

- it can be mixed with request parameters (`$name` in the example above)
- the order of parameters doesn't matter, they are resolved by type-hint (for dependency injection) and by name (for request parameters)
- it only works with objects that you can type-hint: you can't inject string/int values for example, and you can't inject container entries whose name is not a class/interface name (e.g. `twig` or `doctrine.entity_manager`)

### Controllers as services

With Silex and Pimple, you can define [controllers as services](http://silex.sensiolabs.org/doc/providers/service_controller.html) by installing the `ServiceControllerServiceProvider` and using a specific notation.

With the PHP-DI bridge, you can natively define any type of callable based on services:

- object method:

```php
class HelloController
{
    public function helloAction($name)
    {
        // ...
    }
}

$app->get('/{name}', [HelloController::class, 'helloAction']);
```

You will notice above that we give the class name and not an object: PHP-DI will instantiate the instance (and inject dependencies inside it) only if it is used.

- [invocable class](http://php.net/manual/en/language.types.callable.php)

```php
class HelloController
{
    public function __invoke($name)
    {
        // ...
    }
}

$app->get('/{name}', HelloController::class);
```

Again you will notice that we pass the class name and not an instance. PHP-DI will correctly detect that this is an invocable class and will instantiate it.

### Middlewares, route variable converters, error handlers and view handlers

The callable resolution described above (for "controllers as services") applies for registering other Silex objects:

- [middlewares](http://silex.sensiolabs.org/doc/middlewares.html)
- [route variable converters](http://silex.sensiolabs.org/doc/usage.html#route-variable-converters)
- [error handlers](http://silex.sensiolabs.org/doc/usage.html#error-handlers)
- [view handlers](http://silex.sensiolabs.org/doc/usage.html#view-handlers)

For example you can define a middleware like so and let PHP-DI instantiate it:

```php
class AuthMiddleware
{
    public function beforeRoute(Request $request, Application $app)
    {
        // ...
    }
}

$app->before([AuthMiddleware::class, 'beforeRoute']);
```

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

Read more on the [Silex-Bridge project on GitHub](https://github.com/PHP-DI/Silex-Bridge).
