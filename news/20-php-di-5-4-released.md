---
layout: blogpost
title: PHP-DI 5.4 released
author: Matthieu Napoli
date: August 23rd, 2016
---

I am happy to announce that PHP-DI version 5.4 has been released. Here is the list of changes in this new version.

## Factory parameters

Up to PHP-DI 5.3, factories could take as parameters:

- the container
- the requested entry name, by type-hinting `DI\Factory\RequestedEntry`
- services, by type-hinting each service (PHP-DI would then use autowiring)

With PHP-DI 5.4 you can now configure explicitly each parameter of the factory with the following syntax:

```php
return [
    'Database' => DI\factory(function ($host) {
        ...
    })->parameter('host', DI\get('db.host')),
];
```

You can of course mix type-hinted parameters and configured parameters:

```php
return [
    'Database' => DI\factory(function ($host, LoggerInterface $logger) {
        ...
    })->parameter('host', DI\get('db.host')),
];
```

And of course it works with class methods too. A perfect example to illustrate that is [Doctrine's entity manager factory](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html#obtaining-an-entitymanager):

```php
use Doctrine\ORM\EntityManager;

return [
    EntityManager::class => DI\factory([EntityManager::class, 'create'])
        ->parameter('connection', DI\get('db.params'))
        ->parameter('config', DI\get('doctrine.config')),

    'db.params' => [
        'driver'   => 'pdo_mysql',
        'user'     => 'root',
        'password' => '',
        'dbname'   => 'foo',
    ],
    'doctrine.config' => ...,
];
```

You can read the original issue: [#362](https://github.com/PHP-DI/PHP-DI/issues/362), as well as all the pull requests: [#428](https://github.com/PHP-DI/PHP-DI/pull/428), [#430](https://github.com/PHP-DI/PHP-DI/pull/430), [#431](https://github.com/PHP-DI/PHP-DI/pull/431) and [#432](https://github.com/PHP-DI/PHP-DI/pull/432). You can find that feature in the ["factories" documentation](http://php-di.org/doc/php-definitions.html#factories).

This feature was contributed by [@predakanga](https://github.com/predakanga).

## Other improvements

- [#429](https://github.com/PHP-DI/PHP-DI/pull/429): performance improvements in definition resolution.
- [#421](https://github.com/PHP-DI/PHP-DI/issues/421): once a `ContainerBuilder` has built a container, it is locked to prevent confusion when adding new definitions to it.
- [#423](https://github.com/PHP-DI/PHP-DI/pull/423): improved exception messages.

## Other news

In other news the project passed 800 stars on GitHub, has now received contributions from 39 different people and has been installed more than 200 000 times through Packagist since its move to the PHP-DI organization.

PHP-DI is now also used in [**Pimcore**](https://pimcore.org/), an open source content management platform. You can read about it in their documentation: [Dependency injection in Pimcore](https://www.pimcore.org/wiki/pages/viewpage.action?pageId=22282310).

Here is a graph showing the evolution of the traffic (per week) on [PHP-DI's website](http://php-di.org):

![](20-visits.png)

Thanks to all contributors and users!
