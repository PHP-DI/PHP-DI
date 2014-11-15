---
layout: blogpost
title: PHP-DI 4.4 released
author: Matthieu Napoli
date: October 12th 2014
---

I am happy to announce that PHP-DI version 4.4 has been released.
This version contains mainly improvements to the `Container::call()` feature, and contains no BC-break.


## Container::call()

`Container::call()` was introduced in [v4.2](10-php-di-4-2-released.md) and lets you invoke a callable while letting PHP-DI resolve its parameters:

```php
$container->call(function (LoggerInterface $logger) {
    $logger->info('Hello world!');
});
```

In the example above, PHP-DI would resolve the `$logger` dependency using the `LoggerInterface` type-hint and then invoke the closure.

### Support for all types of callables

`Container::call()` works great with closures, but it wasn't completely supporting all the different kinds of *callables*.

PHP-DI 4.4 brings support for every callable type:

- closures
- PHP functions: `'someFunction'` would call `someFunction(…)`
- object method: `[$object, 'method']` would call `$object->method(…)`
- static class method: `['MyClass', 'method']` would call `MyClass::method(…)`
- [invokable object](http://php.net/manual/en/language.oop5.magic.php#object.invoke): `$object` would call `$object(…)`

Invokable objects are objects implementing the `__invoke()` magic method. They can be called directly:

```php
class MyClass {
    public function __invoke() {
        ...
    }
}

$object = new MyClass;
$object();
```

A good example to show how good these improvements are is with the example of a micro-framework:

```php
$controller = /* get from the router */;

$container->call($controller, $_GET + $_POST);
```

This lets you write your controllers as PHP callables. That means you can use functions, closures, object methods or even invokable objects as controllers.

### Auto-creation of callable objects

PHP-DI 4.3 introduced the possibility for auto-creating objects in `Container::call()`:

```php
class MyClass {
    public function method() {
        ...
    }
}

$container->call(['MyClass', 'method']);
```

If `method()` is not a static method, then `MyClass` will be resolved from the container (a new instance will be created automatically). This results in the equivalent code:

```php
(new MyClass)->method();
```

With PHP-DI 4.4, you can now use the same feature for invokable objects:

```php
class MyClass {
    public function __invoke() {
        ...
    }
}

$container->call('MyClass');
```

The example above is equivalent to:

```php
$object = new MyClass;
$object();
```

If we take the "micro-framework" example again, we can turn this:

```php
class HomeController {
    public function __invoke($name) {
        echo 'Hello ' . $name;
    }
}

$controller = $container->make('HomeController');
$container->call($controller, $_GET + $_POST);
```

Into this:

```php
$container->call('HomeController', $_GET + $_POST);
```

Neat!

If you want to learn more, read the [Using the Container](../doc/container.md) documentation.


## Silently ignore phpDoc errors

If you use annotations, PHP-DI reads the phpDoc to guess property and parameter types.

If a dockblock contains non-existent classes like this:

```php
/**
 * @param type $param
 */
public function __construct($param)
{
    // ...
}
```

Then PHP-DI will throw an exception.

Thanks to [@kdubois](https://github.com/kdubois), you now have an option to silently ignore those
errors as long as they do not prevent resolving objects:

```php
$containerBuilder->ignorePhpDocErrors(true);
```

Read more about this in the [Container configuration](../doc/container-configuration.md) documentation.


## Change log

You can read the complete [change log](../change-log.md).
