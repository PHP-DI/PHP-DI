---
layout: blogpost
title: PHP-DI 5.2 released
author: Matthieu Napoli
date: December 20, 2015
---

I am happy to announce that PHP-DI version 5.2 has been released. The main changes in this new versions are:

- new features for factories
- improved documentation
- simpler and clearer exception messages
- bugfixes

## Factories

### Injection in closures

Currently the only way to write factories is to write a callable **that takes the container as the only parameter**:

```php
return [
    'foo' => function(ContainerInterface $c) {
        $bar = $c->get('bar');
        return new Foo($bar);
    },
];
```

To get dependencies in your factory, you could either call `$container->get()` (as shown above) or create a full factory class that can use dependency injection in its constructor.

From 5.2 and above, there is a simpler solution: **you can inject services by type-hint into your closures:**

```php
return [
    'foo' => function(Bar $bar) {
        return new Foo($bar);
    },
];
```

Of course autowiring is limited to container entries you can type-hint (so it will not work with, for example, scalar values), but it will help you write simpler factories most of the time.

Of course this is completely **optional and backward compatible**: you can still inject the container as before. Some work has also been done to ensure this new feature comes with no performance penalty (thanks [Blackfire](https://blackfire.io/)).

### Get the requested name

Sometimes the same factory will be used to create several services, for example for a `RepositoryFactory`, or when using [wildcards in definitions](http://php-di.org/doc/php-definitions.html#wildcards).

In those cases, you can now inject an argument type-hinted as `DI\Factory\RequestedEntry` to get the name of the entry that is being constructed:

```php
return [
    'Acme\Repository\*Repository' => function(EntityManager $entityManager, RequestedEntry $e) {
        $class = $e->getName();
        return new $class($entityManager);
    },
];
```

The `RequestedEntry` object has a very simple API:

```php
interface RequestedEntry
{
    public function getName();
}
```

## Change log

Here is the complete [change log](../change-log.md):

- [#347](https://github.com/PHP-DI/PHP-DI/pull/347) (includes [#333](https://github.com/PHP-DI/PHP-DI/pull/333) and [#345](https://github.com/PHP-DI/PHP-DI/pull/345)): by [@jdreesen](https://github.com/jdreesen), [@quimcalpe](https://github.com/quimcalpe) and [@mnapoli](https://github.com/mnapoli)
    - Allow injection of any container object as factory parameter via type hinting
    - Allow injection of a `DI\Factory\RequestedEntry` object to get the requested entry name
- [#272](https://github.com/PHP-DI/PHP-DI/issues/272): Support `"Class::method""` syntax for callables (by [@jdreesen](https://github.com/jdreesen))
- [#332](https://github.com/PHP-DI/PHP-DI/issues/332): IDE support (plugin and documentation) (by [@pulyaevskiy](https://github.com/pulyaevskiy), [@avant1](https://github.com/avant1) and [@mnapoli](https://github.com/mnapoli))
- [#326](https://github.com/PHP-DI/PHP-DI/pull/326): Exception messages are simpler and more consistent (by [@mnapoli](https://github.com/mnapoli))
- [#325](https://github.com/PHP-DI/PHP-DI/pull/325): Add a "Edit this page" button in the website to encourage users to improve the documentation (by [@jdreesen](https://github.com/jdreesen))
- [#321](https://github.com/PHP-DI/PHP-DI/pull/321): Allow factory definitions to reference arbitrary container entries as callables (by [@jdreesen](https://github.com/jdreesen))
- [#335](https://github.com/PHP-DI/PHP-DI/issues/335): Class imports in traits are now considered when parsing annotations (by [@thebigb](https://github.com/thebigb))

Let's finish on a "Thank you" to all contributors involved in this release, especially [@jdreesen](https://github.com/jdreesen) for his awesome work on several longstanding issues and [@quimcalpe](https://github.com/quimcalpe) for his patience and great contribution to [#347](https://github.com/PHP-DI/PHP-DI/pull/347).
