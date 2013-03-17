# Scopes

By default, PHP-DI return a new instance each time it supplies a value. This behaviour is configurable via scopes.
Scopes allow you to reuse instances and gives you the flexibility to choose the scope of the objects you create through configuration instead of having to 'bake in' the scope of an object at the PHP class level.

The scopes supported out of the box are listed below:

Scope | Desciption
------|------------
singleton | The object instance is unique during the container's lifecycle - each injection by the container or explicit call of `get()` returns the same instance.
prototype (default) | The object instance is not unique - each injection or call of the container's `get()` method returns a fresh instance.

## Applying Scopes

PHP-DI uses annotations to identify scopes. Specify the scope by applying the `@Scope` annotation to the implementation class. All possible values can be obtained from the table above.
Prototype is the default scope and is therefore assumed if no `@Scope` annotation was found.

```php
use DI\Annotations\Scope;

/**
 * A sample class
 *
 * @Scope("singleton")
 */
class MyService {
    // ...
}
```
