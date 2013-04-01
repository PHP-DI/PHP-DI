# Scopes

By default, PHP-DI returns the same instance each time it supplies a value. This behaviour is configurable via scopes.
Scopes allow you to reuse instances and gives you the flexibility to choose the scope of the objects you create through configuration instead of having to 'bake in' the scope of an object at the PHP class level.

The scopes supported out of the box are listed below:

Scope | Description
------|------------
singleton (default) | The object instance is unique during the container's lifecycle - each injection by the container or explicit call of `get()` returns the same instance.
prototype | The object instance is not unique - each injection or call of the container's `get()` method returns a fresh instance.

## Applying Scopes

Scopes are part of the [definitions](doc/definition.md) of injections, so you can define them using annotations or PHP arrays.

### Annotation

You can specify the scope by using the `@Injectable` annotation on the target class.
Remember singleton is the scope used if it is not configured.

```php
use DI\Annotation\Injectable;

/**
 * @Injectable(scope="prototype")
 */
class MyService {
    // ...
}
```

### Array configuration

You can specify the scope by using the `scope` key in the array describing the class:

```php
<?php
return [
    'MyService' => [
        'scope' => Scope::PROTOTYPE(),
        // or
        'scope' => 'prototype',
    ],
];
```
