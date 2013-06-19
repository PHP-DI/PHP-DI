# Definition overriding

PHP-DI provides different definition sources. As explained on the [Definition documentation](documentation.md),
you can combine them by taking advantage of priorities that apply.

From the highest priority to the least:

- Code definition (i.e. defined with `$container->set()`)
- File and array definitions (if A is added after B, then A prevails)
- Annotations
- Reflection

## Example

Here is an example that wouldn't work with Reflection only:

```php
class Foo {
    public function __construct(BarInterface $param1) {
    }
}
```

PHP-DI cannot inject an interface, since it's not instantiable.

While the Reflection definition says that `$param1` should take a `BarInterface` instance, we can use annotations to override that:

```php
use DI\Annotation\Inject;

class Foo {
    /**
     * @Inject({"param1" = "BarImplementation"})
     */
    public function __construct(BarInterface $param1) {
    }
}
```

As explained above, the Annotation definition (`BarImplementation` for `$param1`) has a higher priority
than the Reflection definition (`BarInterface` for `$param1`).

You can go even further, but overriding this definition using file-based definitions:

```yaml
# config/di.yml
Foo:
  constructor:
    param1: AnotherBarImplementation
```

```php
$container->addDefinitionsFromFile(new YamlDefinitionFileLoader('config/di.yml'));
```

And also, override the file-based definition by directly calling the container:

```php
$container->set('Foo')
    ->bindTo('YetAnotherBarImplementation');
```
