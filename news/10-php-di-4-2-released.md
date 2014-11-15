---
layout: blogpost
title: PHP-DI 4.2 released
author: Matthieu Napoli
date: July 29th 2014
---

I am happy to announce that PHP-DI version 4.2 has been released.

It comes with very interesting new features, but first you need to know that it also comes with
a small BC break! This BC break was a necessary evil to fix an buggy and inconsistent behavior:

```php
    public function __construct(Bar $bar = null)
    {
        $this->bar = $bar ?: $this->createDefaultBar();
    }
```

In 4.1, by default PHP-DI would inject a `Bar` instance in the *optional* parameter.
This is not what one would expect, and this was causing unexpected bugs because it turned optional dependencies into required dependencies.

Optional parameters are now ignored (i.e. their default value is injected unless configured otherwise).
You can of course configure explicitly an injection in optional parameters (through PHP config or annotation).


## Container::call()

The container [already provided `get()`, `has()` and `make()`](../doc/container.md).
Now it can also `call` functions using dependency injection.

Example:

```php
$container->call(function (LoggerInterface $logger) {
    $logger->info('Hello world!');
});
```

`Container::call()` will call the given function and resolve the parameters automatically.
Those familiar with AngularJS (for example) will not be lost.

But what's even more interesting is that you can also explicitly set parameters if some are not supposed to be given
by the container:

```php
$parameters = [
    'data' => /* some variable */
];

$container->call(function (LoggerInterface $logger, $data) {
    // ...
}, $parameters);
```

**Any real life example please?**

There are many ways `call()` can become useful, a simple example would be a controller.
In its raw definition, a controller is a simple callable.

For example if you use Silex, a controller is just a closure:

```php
$app->get('/', function(Request $request) use ($app) {
    $twig = $app['twig'];

    return $twig->render('home.twig');
});
```

What the framework does to call the controller is simple:

```php
$request = new Request(/* ... */);
$controller = /* get from the router */;

// Call the controller
$controller($request);
```

What if the MVC framework used `Container::call()`?

```php
$request = new Request(/* ... */);
$controller = /* get from the router */;

// Call the controller
$container->call($controller, [
    'request' => $request
]);
```

That allows to use dependency injection instead of the Service Locator antipattern:

```php
$app->get('/', function(Twig_Environment $twig, Request $request) use ($app) {
    return $twig->render('home.twig');
});
```

And that becomes even more interesting if you controller are classes!
You can have PHP-DI instantiate your controller (using DI) **and** call the method:

```php
class HomeController {
    public function helloAction($name) {
        echo 'Hello ' . $name;
    }
}

$controller = $container->make('HomeController');
$container->call(
    array($controller, 'helloAction'), // this is the callable
    $_GET                              // parameters that can be injected
);
```

What's great using `make()` and `call()`:

- you have dependency injection in your controller class (constructor, property, etc.)
- you can inject services in your actions
- you can also inject request parameters in your actions (here `$_GET` is used for the dirty example)

You can see a more complete (and working) example here: [mnapoli/minimal-app](https://github.com/mnapoli/minimal-app).

Of course, this is just an example, you are not supposed to re-write an MVC framework.
But hopefully it helps to see the potential behind `call()`.

[Read more in the documentation of the container's API](../doc/container.md)


## Wildcards in definitions

You can use wildcards to define a batch of entries. It can be very useful to bind interfaces to implementations:

```php
return [
    'Blog\Domain\*RepositoryInterface' => DI\object('Blog\Architecture\*DoctrineRepository'),
];
```

In our example, the wildcard will match `Blog\Domain\UserRepositoryInterface`, and it will map it to
`Blog\Architecture\UserDoctrineRepository`.

Good to know:

- the wildcard does not match across namespaces
- an exact match (i.e. without `*`) will always be chosen over a match with a wildcard
(first PHP-DI looks for an exact match, then it searches in the wildcards)
- in case of "conflicts" (i.e. 2 different matches with wildcards), the first match will prevail


## Prototype scope for factories

Up till 4.1, factories where only in the Singleton scope: they were only called once.

Now you can use the Prototype scope:

```php
return [
    'foo' => DI\factory(function () {
        return new Foo();
    })->scope(Scope::PROTOTYPE()),
];
```

With this scope, the factory will be called each time `foo` is retrieved.


## 4.1

Are you still on v4.0? Here is a reminder of the cool things included in 4.1:

- HHVM support
- better exception messages
- [container-interop](https://github.com/container-interop/container-interop) compatibility, which means better
integration in other frameworks
- better Symfony 2 documentation


## Change log

Some other minor bugfixes where included in the release.

You can read the complete [change log](../change-log.md).
