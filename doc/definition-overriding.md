---
template: documentation
---

# Definition overriding

PHP-DI provides different definition sources. As explained on the [Definition documentation](definition.md),
you can combine them by taking advantage of priorities that apply.

From the highest priority to the least:

- Explicit definition on the container (i.e. defined with `$container->set()`)
- PHP file definitions (if A is added after B, then A prevails)
- Annotations
- Autowiring

## Example

Given this simple example:

```php
class Foo {
    public function __construct(Bar $param1) {
    }
}
```

PHP-DI would inject an instance of `Bar`. What if we wanted to inject a specific instance?

While autowiring guesses that `$param1` should take a `Bar` instance, we can use annotations to override that:

```php
class Foo {
    /**
     * @Inject({"my.specific.service"})
     */
    public function __construct(BarInterface $param1) {
    }
}
```

As explained above, the Annotation definition (`my.specific.service` for `$param1`) has a higher priority
than the autowiring definition (`Bar` for `$param1`).

You can go even further by overriding this definition using file-based definitions:

```php
# config/di.php

return [
    'Foo' => DI\object()
        ->constructor(DI\link('another.specific.service')),

    'another.specific.service' => DI\object('Bar'),
];
```

Finally, you can also override the file-based definition by directly calling the container:

```php
$container->set('Foo')
    ->constructor(DI\link('yet.another.specific.service'));
```
