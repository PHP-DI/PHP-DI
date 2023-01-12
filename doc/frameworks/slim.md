---
layout: documentation
current_menu: slim
---

# PHP-DI in Slim

[Slim](http://www.slimframework.com/) is a micro-framework for web applications and APIs. Given the framework is compatible with PSR-11, *Slim can work with any container* out of the box. Using PHP-DI with Slim is then easy.

However **the PHP-DI bridge provides several additional features**:

- controllers as services, allowing dependency injection in controllers
- intelligent parameter injections in controller

## Setup

The latest version of the bridge is compatible with Slim v4.2 and up.

```
composer require php-di/slim-bridge
```

Once installed, instead of using the official `Slim\Factory\AppFactory`, instead use PHP-DI's `Bridge` class to create your application:

```php
<?php
require 'vendor/autoload.php';

$app = \DI\Bridge\Slim\Bridge::create();
```

If you want to configure PHP-DI, pass your configured container to the method:

```php
$container = /* create your container */;

$app = \DI\Bridge\Slim\Bridge::create($container);
```

Have a look at [configuring PHP-DI](../container-configuration.md) for details on how to create and configure the container.

You can then use the application [just like a classic Slim application](http://www.slimframework.com/), for example:

```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $response->getBody()->write('Hello!');
    return $response;
});

$app->run();
```

On top of that, extra features from PHP-DI are automatically available. Read the rest of the page to learn more.

## Why use PHP-DI's bridge?

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

// Notice how we register the controller using the class name?
// PHP-DI will instantiate the class for us only when it's actually necessary
$app->delete('/user/{id}', [UserController::class, 'delete']);
```

Dependencies can then be injected in your controller using [autowiring, PHP-DI config files or even PHP attributes](../definition.md).

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

## More

Read more on the [Slim-Bridge project on GitHub](https://github.com/PHP-DI/Slim-Bridge).
