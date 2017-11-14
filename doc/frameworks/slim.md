---
layout: documentation
current_menu: slim
---

# PHP-DI in Slim 3

[Slim 3](http://www.slimframework.com/) is a micro-framework for web applications and APIs. This framework has been one of the first to integrate [container-interop](https://github.com/container-interop/container-interop) in its core. That means that *Slim can work with any container* out of the box.

Replacing Slim's default container with PHP-DI is thus easy. However this bridge provides several additional features *on top of that*:

- controllers as services, allowing dependency injection in controllers
- intelligent parameter injections in controller

### Controllers as services

While your controllers can be simple closures, you can also **write them as classes and have PHP-DI instantiate them only when they are called**:

```php
class UserController
{
    private $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function delete($request, $response)
    {
        $this->userRepository->remove($request->getAttribute('id'));
        
        $response->getBody()->write('User deleted');
        return $response;
    }
}

$app->delete('/user/{id}', ['UserController', 'delete']);
```

Dependencies can then be injected in your controller using [autowiring, PHP-DI config files or even annotations](../definition.md).

### Controller parameters

By default, Slim controllers have a strict signature: `$request, $response, $args`. The PHP-DI bridge offers a more flexible and developer friendly alternative.

Controller parameters can be any of these things:

- the request or response (parameters must be named `$request` or `$response`)
- route placeholders
- request attributes
- services (injected by type-hint)

You can mix all these types of parameters together too. They will be matched by priority in the order of the list above.

#### Request or response injection

You can inject the request or response in the controller parameters by name:

```php
$app->get('/', function (ResponseInterface $response, ServerRequestInterface $request) {
    // ...
});
```

As you can see, the order of the parameters doesn't matter. That allows to skip injecting the `$request` if it's not needed for example.

#### Route placeholder injection

```php
$app->get('/hello/{name}', function ($name, ResponseInterface $response) {
    $response->getBody()->write('Hello ' . $name);
    return $response;
});
```

As you can see above, the route's URL contains a `name` placeholder. By simply adding a parameter **with the same name** to the controller, PHP-DI will directly inject it.

#### Request attribute injection
 
```php
$app->add(function ($request, $response, $next) {
    $request = $request->withAttribute('name', 'Bob');
    return $next($request, $response);
});
 
$app->get('/', function ($name, ResponseInterface $response) {
    $response->getBody()->write('Hello ' . $name);
    return $response;
});
```
 
As you can see above, a middleware sets a `name` attribute. By simply adding a parameter **with the same name** to the controller, PHP-DI will directly inject it.

#### Service injection

To inject services into your controllers, you can write them as classes. But if you want to write a micro-application using closures, you don't have to give up dependency injection either.

You can inject services by type-hinting them:

```php
$app->get('/', function (ResponseInterface $response, Twig $twig) {
    return $twig->render($response, 'home.twig');
});
```

> Note: you can only inject services that you can type-hint and that PHP-DI can provide. Type-hint injection is simple, it simply injects the result of `$container->get(/* the type-hinted class */)`.

## Installation

```
composer require php-di/slim-bridge
```

## Usage

Instead of using `Slim\App`, simply use `DI\Bridge\Slim\App`:

```php
<?php
require 'vendor/autoload.php';

$app = new \DI\Bridge\Slim\App;
```

You can then use the application [just like a classic Slim application](http://www.slimframework.com/):

```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $response->getBody()->write('Hello!');
    return $response;
});

$app->run();
```

You may notice the `DI\Bridge\Slim\App` class is very simple. You can very well create the container yourself and pass it to the constructor of `Slim\App`. Just don't forget to register the [`src/config.php`](https://github.com/PHP-DI/Slim-Bridge/blob/master/src/config.php) file in the container.

### Configuring PHP-DI

If you want to configure PHP-DI, simply extend the `DI\Bridge\Slim\App` class and override the `configureContainer()` method:

```php
class MyApp extends \DI\Bridge\Slim\App
{
    protected function configureContainer(ContainerBuilder $builder)
    {
        $builder->addDefinitions(__DIR__ . 'my-config-file.php');
    }
}

$app = new MyApp;
```

Or if you are using PHP 7 you can use anonymous classes:

```php
$app = new class() extends \DI\Bridge\Slim\App {
    protected function configureContainer(ContainerBuilder $builder)
    {
        $builder->addDefinitions(__DIR__ . 'my-config-file.php');
    }
};
```

Have a look at [configuring PHP-DI](../container-configuration.md) for more details.

### Configuring Slim

```php
// my-config-file.php

return [
    'settings.responseChunkSize' => 4096,
    'settings.outputBuffering' => 'append',
    'settings.determineRouteBeforeAppMiddleware' => false,
    'settings.displayErrorDetails' => false,
    // ...
];
```

### Twig

In order to get you started easily, here is how you can install the Twig extension for Slim:

- install the [Twig-View](https://github.com/slimphp/Twig-View) package:

    ```
    composer require slim/twig-view
    ```
- configure the `Twig` class in PHP-DI (taken from [the package's documentation](https://github.com/slimphp/Twig-View#usage)):

    ```php
    class MyApp extends \DI\Bridge\Slim\App
    {
        protected function configureContainer(ContainerBuilder $builder)
        {
            $definitions = [
            
                \Slim\Views\Twig::class => function (ContainerInterface $c) {
                    $twig = new \Slim\Views\Twig('path/to/templates', [
                        'cache' => 'path/to/cache'
                    ]);
                
                    $twig->addExtension(new \Slim\Views\TwigExtension(
                        $c->get('router'),
                        $c->get('request')->getUri()
                    ));
                
                    return $twig;
                },
                
            ];
            
            $builder->addDefinitions($definitions);
        }
    }
    ```

You can now inject the service in your controllers and render templates:

```php
$app->get('/', function ($response, Twig $twig) {
    return $twig->render($response, 'home.twig');
});
```

## More

Read more on the [Slim-Bridge project on GitHub](https://github.com/PHP-DI/Slim-Bridge).
