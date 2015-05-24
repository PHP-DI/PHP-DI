---
layout: blogpost
title: PHP-DI 5.0 released
author: Matthieu Napoli
date: May 14th 2015
---

I am very excited to announce that, after 8 months of iterative work, PHP-DI 5.0 is released.

This is a new major version that comes with:

- minor backward compatibility breaks (nothing to get too alarmed about)
- API and syntax simplifications (aka syntactic sugar)
- new major features for modular applications (aka bundles, modules, plugins, …)
- performance improvements (thanks [Blackfire](https://blackfire.io/)) and a lighter package
- a new website, logo and half rewritten documentation
- Silex integration

But the best news of all is that this new major version wasn't produced out of thin air: it has been used in [Piwik](https://piwik.org/) and developed as a rolling release (i.e. always stable) to test and evaluate new features hands on. Because of that, PHP-DI 5 has been in production since January 2015. **More that 100 000 Piwik installs in the wild are running PHP-DI 5 already!**

So let's start and have a look at what's new.

## BC breaks

PHP-DI 5 doesn't break major features so you will not have a lot of work to upgrade. Here are the main changes:

- PHP 5.3 is no longer supported (5.4 or above is required)
- annotations are now disabled by default (enabling them is one line of code)
- lazy injection is disabled by default for a much lighter package
- `DI\link()` is deprecated in favor of a new `DI\get()` (but still works, so not really a BC break)

The complete list of changes are detailed extensively in the [migration guide to 5.0](../doc/migration/5.0.md).

## API simplifications, aka syntactic sugar

PHP-DI wants to be the DI container *for humans*, simplicity of use is one of its most important feature. To improve the situation even more, a few improvements have been made.

Most of these changes have been explained in the previous blog article: [Syntactic sugar in PHP-DI 5](14-php-di-5-syntaxic-sugar.md). Here is a reminder of the most important improvements:

```php
<?php

use ...;
// PHP 5.6 example
use function ...;

return [
    'path.root' => __DIR__ . '/..',

    // new DI\string() helper to write string expressions
    'path.cache' => string('{path.root}/var/cache'),

    // DI\factory() is now optional when using closures
    PdfGenerator::class => function () {
        return ...;
    },

    // array of services, yay!
    'notification.handlers' => [
        get(EmailHandler::class),
        get(TextHandler::class),
        get(PushHandler::class),
    ],

    // nest definitions in other definitions!
    Cache::class => object(JsonFileCache::class)
        ->constructor(string('{path.cache}/cache.json')),
];
```

## Features for modular applications

The main motivation behind PHP-DI 5 was to improve support for scenarios involving several configuration files. The best illustration for this are application built using modules/bundles/plugins, which is exactly what was needed for Piwik and its plugin system.

### Lists

Playing with lists is one of the most important feature. It involves defining array of services as well as adding new items to an existing array.

Let's illustrate that with an example: your application can support many "authentication providers". By default, you can sign up to the application and create an account which will be stored in a database (using an email and a password):

```php
// application config file
return [
    'auth.providers' => [
        get(DatabaseAuthProvider::class),
    ],
];
```

However you can have modules that can provide new authentication systems:

```php
// Facebook login module
return [
    'auth.providers' => DI\add([
        get(FacebookAuthProvider::class),
    ]),
];
```

```php
// Twitter login module
return [
    'auth.providers' => DI\add([
        get(TwitterAuthProvider::class),
    ]),
];
```

Those modules can be registered by simply adding the configuration files:

```php
$builder = new ContainerBuilder();

$builder->addDefinitions('app/config.php');
$builder->addDefinitions('src/FacebookModule/config.php');
$builder->addDefinitions('src/TwitterModule/config.php');
```

As a result getting the `auth.providers` list will return the 3 items merged:

```php
[
    get(DatabaseAuthProvider::class),
    get(FacebookAuthProvider::class),
    get(TwitterAuthProvider::class),
]
```

For those familiar with Symfony, the same result can be achieved using [tags](http://symfony.com/doc/current/components/dependency_injection/tags.html). The approach chosen for PHP-DI is a little different for multiple reasons:

- adding an item to an array is more similar to what we do in vanilla PHP
- tags require to write [compiler passes](http://symfony.com/doc/current/components/dependency_injection/tags.html#create-a-compilerpass) which are verbose and not trivial
- tags don't work if the container isn't compiled

In the end manipulating lists instead of tags is simpler and feels more natural to use. To be fair however tags offer an approach with more freedom, allowing to implement more advanced behaviors.

### Decorating a previous entry

A new `DI\decorate()` helper was added, allowing to decorate a previous entry using a closure. A common scenario for using this is to override object using the [decorator pattern](http://en.wikipedia.org/wiki/Decorator_pattern).

Here is an example where a module replaces the default "Product DAO" for by decorating it with a cache wrapper:

```php
// application config
ProductDaoInterface::class => DI\get(ProductDaoMySQL::class)
```

```php
// module config
ProductDaoInterface::class => DI\decorate(function ($previous, ContainerInterface $c) {
    return new ProductDaoCached($previous);
})
```

The first argument of the closure is the previous object (the one we decorate), the second argument is the container.

The example above is equivalent to:

```php
$dao = new ProductDaoCached(new ProductDaoMySQL());
```

## Silex integration

A new framework integration comes with this new version: the [Silex](http://silex.sensiolabs.org/) micro-framework. If you are interested to learn about it, head over to [the documentation](../doc/frameworks/silex.md).

## Wrapping it up

I hope you will like this new version, as well as the new website. If you want the complete list of changes, head over to [the change log](../change-log.md).

If something isn't right in the package or the documentation, please [open an issue](https://github.com/mnapoli/PHP-DI/issues/new). You can also find support in the [Gitter chatroom](https://gitter.im/mnapoli/PHP-DI).
