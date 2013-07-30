# PHP-DI 3.3 released

*Posted by [Matthieu Napoli](https://github.com/mnapoli) on July 30rd 2013*

I am happy to announce that PHP-DI version 3.3 has just been released.

The major new feature is the possibility to inject all dependencies of **an existing instance**.

## Container::injectOn()

```php
$myObject = new MyClass();

$container->injectOn($myObject);
```

Now, `$myObject` has all its dependencies injected (setter injections and property injections).

It is basically the same as `$myObject = $container->get('MyClass')` except **you** create the instance.

### Why?

Weird right? Well it's not supposed to be used everywhere.

Hopefully, that will help **a lot** to integrate PHP-DI with other frameworks:

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

**Of course**, the preferred method is still to use `$container->get()`. But sometimes you can't get to the root of the framework to intercept the creation of your objects (I'm looking at you most MVC frameworks, PHPUnit & …)

### Good to know

PHP-DI will not perform any constructor injection. But of course most of the time you'll be using that feature is when you can't/don't want to use the constructor for getting your dependencies.


## Change log

Except this feature, 3.3 contains an optimization for definition resolution and a bugfix for a rare situation.

Read all the changes and their authors in the [change log](../change-log.md).
