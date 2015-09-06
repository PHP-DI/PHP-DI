---
layout: documentation
---

# Definition extensions and overriding

A simple application usually takes advantage of one or two *definition sources*: autowiring (or annotations) + a definition file/array.

However in more complex application or modular systems you might want to have multiple definition files (e.g. one per modules/bundles/plugins/…). In this case, PHP-DI provides a clear and powerful system to **override and/or extend definitions**.

## Priorities of definition sources

From the lowest priority to the highest:

- autowiring if enabled
- annotations if enabled
- PHP definitions (file or array) in the order they were added
- definitions added straight in the container with `$container->set()`

### Example

```php
class Foo
{
    public function __construct(Bar $param1)
    {
    }
}
```

PHP-DI would inject an instance of `Bar` using autowiring. Annotations have a higher priority, we can use it to override the definition:

```php
class Foo
{
    /**
     * @Inject({"my.specific.service"})
     */
    public function __construct(Bar $param1)
    {
    }
}
```

You can go even further by overriding annotations and autowiring using file-based definitions:

```php
return [
    'Foo' => DI\object()
        ->constructor(DI\get('another.specific.service')),
    // ...
];
```

If we had another definition file (registered after this one), we could override the definition again.

## Extending definitions

### Objects

A `DI\object()` definition **always extends a previous object definition**. The reason for this is to allow to easily extend autowiring and annotations definitions:

```php
class Foo
{
    public function __construct(Bar $param1, $param2)
    {
    }
}

return [
    Foo::class => DI\object()
        ->constructorParameter('param2', 'Hello!'),
];
```

In this example we extend the autowiring definition to set `$param2` because it can't be guessed through autowiring (no type-hint).

You can also take advantage of this when using multiple definition files:

```php
return [
    Database::class => DI\object()
        ->constructor('localhost', 3306)
        ->method('setLogger', DI\get('logger.default')),
];
```

```php
return [
    // Override only the first constructor parameter
    Database::class => DI\object()
        ->constructorParameter('host', '192.168.34.121'),
];
```

Since `DI\object()` extends (instead of overriding) we have only replaced one constructor parameter. The rest is preserved.

**`DI\object()` is the only kind of definition that extends by default:** all other definitions override the previous definition completely.

### Arrays

You can add entries to an array defined in another file/array using the `DI\add()` helper:

```php
return [
    'array' => [
        DI\get(Entry::class),
    ],
];
```

```php
return [
    'array' => add([
        DI\get(NewEntry::class),
    ]),
];
```

When resolved, the array will contain the 2 entries. **If you forget to use `DI\add()`, the array will be overridden entirely!**

### Decorators

You can use `DI\decorate()` to decorate an object:

```php
return [
    ProductRepository::class => function () {
        return new DatabaseRepository();
    },
];
```

```php
return [
    ProductRepository::class => DI\decorate(function ($previous, ContainerInterface $c) {
        // Wrap the database repository in a cache proxy
        return new CachedRepository($previous);
    }),
];
```

The first parameter of the callable is the instance returned by the previous definition (i.e. the one we wish to decorate), the second parameter is the container.

You can use `DI\decorate()` over any kind of previous definition (factory but also object, value, environment variable, …).
