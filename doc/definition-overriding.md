---
layout: documentation
current_menu: definition-overriding
---

# Definition extensions and overriding

A simple application usually takes advantage of one or two *definition sources*: autowiring (or annotations) + a definition file/array.

However in more complex applications or modular systems you might want to have multiple definition files (e.g. one per modules/bundles/plugins/…). In this case, PHP-DI provides a clear and powerful system to **override and/or extend definitions**.

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
    'Foo' => DI\create()
        ->constructor(DI\get('another.specific.service')),
    // ...
];
```

If we had another definition file (registered after this one), we could override the definition again.

## Extending definitions

### Objects

`DI\create()` overrides completely any previous definition or even autowiring. It doesn't allow extending another definition. See the "decorators" section below if you want to do that.

If an object is built using autowiring (or annotations), you can override specific parameters with `DI\autowire()`:

```php
class Foo
{
    public function __construct(Bar $param1, $param2)
    {
    }
}

return [
    Foo::class => DI\autowire()
        ->constructorParameter('param2', 'Hello!'),
];
```

In this example we extend the autowiring definition to set `$param2` because it can't be guessed through autowiring (no type-hint). `$param1` is not affected and is autowired.

Please note that `DI\autowire()`, like `DI\create()`, does not allow extending definitions. It only allows to customize how autowiring is done. In the example below the second definition will completely override the first one:

```php
return [
    Database::class => DI\autowire()
        ->constructorParameter('host', '192.168.34.121'),
];
```

```php
return [
    Database::class => DI\autowire()
        ->constructorParameter('port', 3306),
];
```

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
    'array' => DI\add([
        DI\get(NewEntry::class),
    ]),
];
```

When resolved, the array will contain the 2 entries. **If you forget to use `DI\add()`, the array will be overridden entirely!**

Note that you can use `DI\add()` even if the array was not declared before.

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
