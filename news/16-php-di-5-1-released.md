---
layout: blogpost
title: PHP-DI 5.1 released
author: Matthieu Napoli
date: September 8th 2015
---

I am happy to announce that PHP-DI version 5.1 has been released. This new version comes with:

- many performances improvements - some benchmarks show up to 35% performance improvements, real results will vary of course
- [Zend Framework 2 integration](../doc/frameworks/zf2.md)
- factory resolution by the container
- many documentation improvements
- several bugfixes

## Factory resolution

*Note: examples below use the [PHP 5.6 shortcut](http://php.net/manual/en/migration56.new-features.php#migration56.new-features.use) `factory()` instead of `DI\factory()`.*

You can define a service using a factory, the simplest example would be using a closure:

```php
return [
    'db' => factory(function ($container) {
        return ...;
    }),
];
```

It can be sometimes useful to put such code in its own class, for example:

```php
class DbFactory
{
    public function create($container)
    {
        return ...;
    }
}
```

You could configure this like so:

```php
return [
    'db' => factory([new DbFactory(), 'create']),
];
```

But it comes with its problems:

- the `DbFactory` object is created for every request, even if not used
- you can't pass dependencies to the factory (you only get the `$container` parameter)

From v5.1 and up you can now ask PHP-DI to create the factory object on-demand (with the benefits of using dependency injection there too):

```php
return [
    'db' => factory(['DbFactory', 'create']),
    // or with PHP 5.5
    'db' => factory([DbFactory::class, 'create']),
];

// You can use dependency injection in the factory now:
class DbFactory
{
    private $dependency;

    public function __construct(Foo $dependency)
    {
        $this->dependency = $dependency;
    }

    public function create()
    {
        return ...;
    }
}
```

As you can see above, it uses the notation of a [PHP callable](http://php.net/manual/en/language.types.callable.php) except the first array item is a class name (or any container entry) instead of the actual instance: `['DbFactory', 'create']`.

To give you an idea, `factory(['DbFactory', 'create'])` is equivalent to the following code:

```php
$factory = $container->get('DbFactory');
$factory->create();
```

(technically the `$container` is passed in `create()` but you probably don't need it)

Two things to note:

- if `DbFactory::create()` is a **static** method then the object will not be created: `DbFactory::create()` will be called statically (as you would expect)
- you can set any container entry name in the array, e.g. `factory(['foo_bar_baz', 'create'])`, allowing you to configure `foo_bar_baz` and its dependencies like any other object

## Change log

If you want the complete list of changes in this new version, head over to [the change log](../change-log.md).

Let's finish on a "Thank you" to all contributors involved in this release!
