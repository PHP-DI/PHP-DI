---
layout: documentation
---

# Lazy injection

Consider the following example:

```php
<?php
class Foo
{
    private $a;
    private $b;

    public function __construct(Foo $a, Bar $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function doSomething()
    {
        $this->a->doStuff();
    }

    public function doSomethingElse()
    {
        $this->b->doStuff();
    }
}
```

`a` is used only when `doSomething()` is called, and `b` only when `doSomethingElse()` is called.

You may wonder then: why injecting `a` **and** `b` if they may not be used? Especially if creating those objects is heavy in time or memory.

That's where lazy injection can help.

## How it works

If you define an object as "lazy", PHP-DI will inject:

- the object, if it has already been created
- or else a **proxy** to the object, if it is not yet created

The proxy is a special kind of object that **looks and behaves exactly like the original object**, so you can't tell the difference. The proxy will instantiate the original object only when needed.

Creating a proxy is complex. For this, PHP-DI relies on [ProxyManager](https://github.com/Ocramius/ProxyManager), the (amazing) library used by Doctrine, Symfony and Zend.

### Example

For the simplicity of the example, we will not inject a lazy object, but we will ask the container to return one:

```php
class Foo
{
    public function doSomething()
    {
    }
}

$container->set('Foo', \DI\object()->lazy());

// $proxy is a Proxy object, it is not initialized
// It is very lightweight in memory
$proxy = $container->get('Foo');

var_dump($proxy instanceof Foo); // true

// Calling a method on the proxy will initialize it
$proxy->doSomething();
// Now the proxy is initialized, the real instance of Foo has been created and called
```

## How to use

You can define an object as "lazy". If it is injected as a dependency, then a proxy will be injected instead.

### Installation

Lazy injection requires the [Ocramius/ProxyManager](https://github.com/Ocramius/ProxyManager) library. This library is not installed by default with PHP-DI, you need to require it in your `composer.json`:

```json
{
    "require": {
        "php-di/php-di": "*",
        "ocramius/proxy-manager": "~1.0"
    }
}
```

Then run `composer update`.

### Annotations

```php
/**
 * @Injectable(lazy=true)
 */
class MyClass
{
}
```

### PHP code

```php
<?php
$containerPHP->set('foo', \DI\object('MyClass')->lazy());
```

### PHP configuration file

```php
<?php

return [
    'foo' => DI\object('MyClass')
        ->lazy(),
];
```
