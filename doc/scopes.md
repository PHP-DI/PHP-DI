---
layout: documentation
current_menu: scopes
---

# Scopes

By default, PHP-DI will inject **the same instance of an object** everywhere.

```
class UserManager
{
    public function __construct(Database $db) {
        // ...
    }
}

class ArticleManager
{
    public function __construct(Database $db) {
        // ...
    }
}
```

In the example above the same `Database` object will be injected if both `UserManager` and `ArticleManager` are instantiated (or injected).

The same rule apply when getting twice the same entry from the container:

```php
$object1 = $container->get('Database');
$object2 = $container->get('Database');

// $object1 === $object2
```

The reason for this behavior is:

- we sometimes need to ensure that only one instance of a class exist (because for example we want to share the same database connection between classes)
- more generally there is no reason to create a new instance every time we want to use a class

If injectable classes (aka services) are correctly designed, they should be [**stateless**](https://igor.io/2013/03/31/stateless-services.html). That means that reusing the same instance in several places of the application doesn't have any side effect.

This behavior is however configurable using **scopes**.

## Available scopes

Container entries can have the following scopes:

- **singleton** (applied by default for every container entry)

    The object instance is unique (shared) during the container's lifecycle - each injection by the container or explicit call of `get()` returns the same instance.

    Please note that while this scope is named "singleton", it is not related to [the Singleton design pattern](http://en.wikipedia.org/wiki/Singleton_pattern).

- **prototype**

    The object instance is not unique - each injection or call of the container's `get()` method returns a new instance.

## Configuring the scope

Scopes are part of the [definitions](definition.md) of injections, so you can define them using annotations or PHP configuration.

### PHP configuration

You can specify the scope by using the `scope` method:

```php
return [
    // A new object will be created every time it is used
    'MyClass1' => DI\object()
        ->scope(Scope::PROTOTYPE),

    // The closure will be called every time MyClass2 is used (and return a new object every time)
    'MyClass2' => DI\factory(function () {
        return new MyClass2();
    })->scope(Scope::PROTOTYPE),
];
```

Remember singleton is the default scope. The only exception is when aliasing an entry to another:

```php
return [
    'Foo' => DI\get('Bar'),
];
```

The alias will be cached, instead it will be resolved every time because the target entry (`Bar`) could have the "prototype" scope.

### Annotation

You can specify the scope by using the `@Injectable` annotation on the target class. Remember singleton is the default scope.

```php
/**
 * @Injectable(scope="prototype")
 */
class MyService
{
    // ...
}
```
