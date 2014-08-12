---
template: blogpost
title: PHP-DI 4.3 released
author: Matthieu Napoli
date: August 12th 2014
---

I am happy to announce that PHP-DI version 4.3 has been released. This version contains features that have all been
implemented by contributors other than me, this is very encouraging :) This is also the fastest version to be released,
since 4.2 was released only 14 days ago.

It comes without any BC break, and with a new main feature: `DI\env()` to import **environment variables**.


## DI\env()

*contributed by [@jmalloc](https://github.com/jmalloc)*

If you use environment variables, you might end up creating several container definitions looking like this:

```php
return [

    'db.url' => DI\factory(
        function (DI\Container $container) {
            $url = isset($_SERVER['DATABASE_URL'])
                ? $_SERVER['DATABASE_URL']
                : 'postgresql://user:pass@localhost/db';
        }
    )

];
```

This might be a common situation for other developers, especially those deploying their applications to
systems like Heroku, or otherwise following the principles of [12-factor applications](http://12factor.net/config).

Since 4.3, this can be written like this:

```php
return [
    'db.url' => DI\env('DATABASE_URL', 'postgresql://user:pass@localhost/db'),
];
```

The first parameter to `DI\env()` is the name of the environment variable to read,
the second parameter is the default value to use in the event that the environment variable is not defined.

If the environment variable is not defined and no default is provided, a `DefinitionException`
is thrown when attempting to resolve the value.

Finally, default values can reference other definitions using `DI\link()`:

```php
return [
    'default-dsn' => 'postgresql://user:pass@localhost/db',
    'dsn' => DI\env('DATABASE_URL', DI\link('my-app.default-dsn')),
];
```


## Container::call() resolves array callable using class name

*contributed by [@thispagecannotbefound](https://github.com/thispagecannotbefound)*

This change will allow you to call an array callable using a class name, like so:

```php
$container->call(['MyClass', 'method]);
```

If the method is not static, `MyClass` will be resolved using the container.


## `DI\FactoryInterface` and `DI\InvokerInterface` are auto-registered

*contributed by [@drealecs](https://github.com/drealecs)*

Before 4.3, if you wanted to use `DI\FactoryInterface` or `DI\InvokerInterface`, you needed to define
them in the configuration.

They are now auto-registered (just like `DI\ContainerInterface`) as links to `DI\Container` so that
you can inject them without any configuration needed.

If you had previously defined them, there is no problem: your definitions always override the internal ones.

Don't know what these interfaces are for? [Check out the "Container API" documentation](http://php-di.org/doc/container.html).


## Change log

You can read the complete [change log](../change-log.md).
