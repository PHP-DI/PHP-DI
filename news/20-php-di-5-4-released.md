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

You can read the original issue: [#362](https://github.com/PHP-DI/PHP-DI/issues/362), as well as all the pull requests: [#428](https://github.com/PHP-DI/PHP-DI/pull/428), [#430](https://github.com/PHP-DI/PHP-DI/pull/430), [#431](https://github.com/PHP-DI/PHP-DI/pull/431) and [#432](https://github.com/PHP-DI/PHP-DI/pull/432). You can find that feature in the ["factories" documentation](http://php-di.org/doc/php-definitions.html#factories).

This feature was contributed by [@predakanga](https://github.com/predakanga).

## Other improvements

- [#429](https://github.com/PHP-DI/PHP-DI/pull/429): performance improvements in definition resolution.
- [#421](https://github.com/PHP-DI/PHP-DI/issues/421): once a `ContainerBuilder` has built a container, it is locked to prevent confusion when adding new definitions to it.
- [#423](https://github.com/PHP-DI/PHP-DI/pull/423): improved exception messages.
