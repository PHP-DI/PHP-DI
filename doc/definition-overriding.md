# Definition overriding

PHP-DI provides different definition sources. As explained on the [Definition documentation](documentation.md),
you can combine them by taking advantage of priorities that apply.

From the highest priority to the least:

- Code definition (i.e. defined with `$container->set()`)
- File and array definitions (if A is added after B, then A prevails)
- Annotations
- Reflection

## Example

Given this simple example:

```php
class Foo {
    public function __construct(Bar $param1) {
    }
}
```

PHP-DI would inject an instance of `Bar`. What if we wanted to inject a specific instance?

While the Reflection definition says that `$param1` should take a `Bar` instance, we can use annotations to override that:

```php
class Foo {
    /**
     * @Inject({"param1" = "my.specific.service"})
     */
    public function __construct(BarInterface $param1) {
    }
}
```

As explained above, the Annotation definition (`my.specific.service` for `$param1`) has a higher priority
than the Reflection definition (`Bar` for `$param1`).

You can go even further by overriding this definition using file-based definitions:

```yaml
# config/di.yml
Foo:
  constructor:
    param1: another.specific.service

another.specific.service:
  class: Bar
  # ... (definition of my specific instance)
```

```php
$container->addDefinitionsFromFile(new YamlDefinitionFileLoader('config/di.yml'));
```

Finally, you can also override the file-based definition by directly calling the container:

```php
$container->set('Foo')
    ->withConstructor(array('param1' => 'yet.another.specific.service'));
```
