---
layout: blogpost
title: PHP-DI 6.0 released
author: Matthieu Napoli
date: February 20th 2018
---

I am very happy to announce that PHP-DI 6.0 has been released!

**If you have never heard of PHP-DI before visit [the home page](../) first** to get an overview of what PHP-DI can do for you.

This new major version focuses on two goals:
- **performances**
- **simplicity**

The article below details what's new, if you want to upgrade there is an [**upgrade guide**](../doc/migration/6.0.md) waiting for you.

## Performances

A lot of work has been put into making PHP-DI one of the fastest DI container. This was achieved through two separate efforts:

- **compiling the container**

    This was detailed in the previous blog article: [*Turning into a compiled container for maximum performances*](21-php-di-6-compiled-container.md). If you want to use that feature right now check out the [*Performances*](../doc/performances.md) documentation.

- **micro-optimizations**

    Micro-optimizations are often overkill, but in the case of a dependency injection container they turned out to make the container faster in many use cases (especially those not covered by the compilation). I want to take the opportunity to thank [Blackfire.io](https://blackfire.io) for creating such a useful profiling tool and for sponsoring a plan for PHP-DI.

## Simplicity

PHP-DI 6 fixes weird and unpredictable subtleties in its configuration format.

### object() becomes create() and autowire()

The `DI\object()` function helper has been replaced by two separate helpers:

- `DI\create()`
- `DI\autowire()`

The `object()` helper was useful but complex: it automatically extended the previous definition, which could be the autowiring definition or a previous definition in another file. If that sentence doesn't make any sense to you that's alright. This was too complicated and it sometimes lead to unpredictable results.

PHP-DI 6 is simpler and more explicit:

- **`create()`**: creates an object (that's it, nothing more)
- **`autowire()`**: autowires an object and allows you to override some parameters

This is much simpler. And of course, you can still use autowiring without having to define every object in the configuration. You should use `autowire()` only when you need to override some parameters.

Both `create()` and `autowire()` keep the same API so you only need to change the function called:

```php
return [

    // Using autowiring
    Mailer::class => DI\autowire()
        // Define a single constructor parameter (the rest is autowired)
        ->constructorParameter('host', 'smtp.example.com'),
        
    // Without autowiring
    Logger::class => DI\create()
        ->constructor('/tmp/app.log')
        ->method('setLevel', 'warning'),
        
];
```

Unsure how to upgrade? Don't worry it is easy, the [upgrade guide](../doc/migration/6.0.md) covers it.

### Nested definitions

Another big problem with PHP-DI 5 was how it handled nested definitions. Some were supported, some weren't, and it wasn't always consistent.

PHP-DI 6 makes it simpler and consistent: **all definitions can now be nested**. On top of that, closures are now always interpreted as factories. This makes a lot of use cases more practical, for example:

```php
return [

    // Define an autowired dependency inline (without having to define a separate entry):
    Foo::class => create()
        ->constructor(autowire(Bar::class)),

    // Use the string() helper inline:
    Foo::class => create()
        ->constructor(string('{tmpDirectory}/test.json')),

    // Use a factory (closure) to define the default value for an environment variable:
    'db.name' => env('DB_NAME', function ($container) {
        return $container->get('db.prefix') . '_foo';
    }),
    
    // Nest definitions in arrays to create lists of services
    'log.preprocessors' => [
        function () {
            return new LogLevelFilter(...);
        },
        function () {
            return new MessageFormatter(...);
        },
    ],

];
```

### Scopes are removed

Scopes have been removed as they are out of the scope of a container. To be more clear, the `prototype` scope cannot be used anymore, the `singleton` scope is now used everywhere.

I've been maintaining PHP-DI for 6 years now and I have never seen a valid use case for the *prototype* scope. [Igor Wiedler wrote about stateless services in 2013](https://igor.io/2013/03/31/stateless-services.html) already. We need to stop using the `get()` method as a factory or worse, a locator for stateful resources like an HTTP request.

Also, that made the codebase simpler and faster :)

Read more details and alternatives in the [scopes](../doc/scopes.md) documentation.

### `new Container`

The container can be configured using the [`ContainerBuilder` class](../doc/container-configuration.md). But if you simply want to get started with the defaults, you can now create the container without having to pass any parameter:

```php
// Before
$builder = new \DI\ContainerBuilder();
$container = $builder->build();

// After
$container = new Container;
```

It's a very small change but it should make it a bit more pleasant to use in small projects.

## Wrapping it up

I hope you enjoy this new release as much as I enjoyed working on it for the last year. Thank you for showing more and more support for the project, this new release got code contributions from 8 contributors as well as ideas and support from even more. Thank you.

As always, if you want the complete list of changes head over to [the change log](../change-log.md). If you are migrating from a 5.x version have a look at the detailed [migration guide](../doc/migration/6.0.md).

If something isn't right in the package or the documentation, please [open an issue](https://github.com/PHP-DI/PHP-DI/issues/new) or a pull request. You can also find support in the [Gitter chatroom](https://gitter.im/PHP-DI/PHP-DI).
