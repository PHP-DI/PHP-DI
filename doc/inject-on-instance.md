---
layout: documentation
---

# Inject on an existing instance

*Feature introduced in PHP-DI 3.3*

The standard way of using a container is to get an object from it, with all its dependencies injected:

```php
$object = $container->get('foo');
```

But in some situations, you don't have the control of the creation of an object, yet **you want to resolve its dependencies**.

PHP-DI offers the `injectOn` method:

```php
// $object is an instance of some class

$container->injectOn($object);
```

Now, `$object` has all its dependencies injected (through setter injections and property injections).


## Constructor injection

PHP-DI will not perform any constructor injection (because the instance is already created).

If you create the object yourself, you'll have to do the constructor injection yourself:

```php
$object = new MyClass($someDependency, $someOtherDependency);
$container->injectOn($object);
```

If you get the object from some library/framework, then just call `injectOn()`


### Why?

Hopefully, that will help to integrate PHP-DI with other frameworks:

- **MVC frameworks** (Symfony 2, Zend Framework 2, …): inject dependencies of the controller, in the controller itself.
- **Tests** (PHPUnit, …): inject tools in your test class, for example a logger, a timer (for performance test), **the entity manager** (for integration tests), …

Example:

```php
class MyController {
    public function __construct() {
        // get container ...
        $container->injectOn($this);
    }
}
```

**Of course**, the preferred method is still to use `$container->get()`. But sometimes you can't get to the root of the framework to intercept the creation of your objects.
