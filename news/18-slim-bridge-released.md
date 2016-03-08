---
layout: blogpost
title: Introducing the PHP-DI bridge for Slim
author: Matthieu Napoli
date: March 8, 2016
-------------------

[Slim 3](http://www.slimframework.com/) was released 3 months ago and it was significant. It is one of the first frameworks to integrate the latest standards and concepts in its core:

- [PSR-7](http://www.php-fig.org/psr/psr-7/), a standard for HTTP messages like requests and responses
- [PSR-7 based middlewares](https://mwop.net/blog/2015-01-08-on-http-middleware-and-psr-7.html), allowing framework-agnostic HTTP layers or even applications
- [container-interop](https://github.com/container-interop/container-interop), a standard for decoupling frameworks from dependency injection containers

While PSR-7 and middlewares are very interesting, what's more useful for us is the use of **container-interop**. That means Slim 3 can work with any dependency injection container.

It is very easy to replace the default container (Pimple) with PHP-DI, but today we are releasing a "PHP-DI - Slim" bridge that goes a little further. **Read below for an introduction of what's possible with the PHP-DI bridge**.

You can also read **[the full documentation for PHP-DI in Slim](../doc/frameworks/slim.md)**.

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

Dependencies can then be injected in your controller using [autowiring, PHP-DI config files or even annotations](../doc/definition.md).

### Controller parameters

By default, Slim controllers have a strict signature: `$request, $response, $args`. The PHP-DI bridge offers a more flexible and developer friendly alternative.

Controller parameters can be any of these things:

- request or response injection (parameters must be named `$request` or `$response`)
- request attribute injection
- service injection (by type-hint)

You can mix all these types of parameters together too. They will be matched by priority in the order of the list above.

#### Request or response injection

You can inject the request or response in the controller parameters by name:

```php
$app->get('/', function (ResponseInterface $response, ServerRequestInterface $request) {
    // ...
});
```

As you can see, the order of the parameters doesn't matter. That allows to skip injecting the `$request` if it's not needed for example.

#### Request attribute injection

```php
$app->get('/hello/{name}', function ($name, ResponseInterface $response) {
    $response->getBody()->write('Hello ' . $name);
    return $response;
});
```

As you can see above, the route's URL contains a `name` placeholder. By simply adding a parameter **with the same name** to the controller, PHP-DI will directly inject it.

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

## Learn more

You can read **[the full documentation for PHP-DI in Slim](../doc/frameworks/slim.md)**.
