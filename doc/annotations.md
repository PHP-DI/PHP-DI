---
layout: documentation
---

# Annotations

Annotations **are disabled by default**. You need to [configure the `ContainerBuilder`](container-configuration.md) to use them:

```php
$containerBuilder->useAnnotations(true);
```

Annotations are written in PHP docblock comments. They are used by a lot of modern libraries and frameworks, like [Doctrine](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html), [Symfony](http://symfony.com/), [Flow](http://flow.typo3.org/), [PHPUnit](http://www.phpunit.de/manual/3.7/en/)…

## Inject

`@Inject` let's you define where PHP-DI should inject something, and what should it inject. You can combine it with `@var` and `@param` phpdoc tags to define what should be injected.

It can be used on:

- the constructor (constructor injection)
- methods (setter/method injection)
- properties (property injection)

Here is an example of all possible uses of the `@Inject` annotation:

```php
class Example
{
    /**
     * Annotation combined with phpdoc:
     *
     * @Inject
     * @var Foo
     */
    private $property1;

    /**
     * Explicit definition of the entry to inject:
     *
     * @Inject("db.host")
     */
    private $property2;

    /**
     * Annotation combined with phpdoc:
     *
     * @Inject
     * @param Foo $param1
     * @param Bar $param2
     */
    public function __construct($param1, $param2)
    {
    }

    /**
     * Annotation combined with the type-hint:
     *
     * @Inject
     */
    public function method1(Foo $param)
    {
    }

    /**
     * Explicit definition of the entries to inject:
     *
     * @Inject({"db.host", "db.name"})
     */
    public function method2($param1, $param2)
    {
    }
}
```

*Note: importing annotations with `use DI\Annotation\Inject;` is optional.*

## Injectable

The `@Injectable` annotation let's you set options on injectable classes:

```php
/**
 * @Injectable(scope="prototype", lazy=true)
 */
class Example {
}
```

**The `@Injectable` annotation is optional: by default, all classes are injectable.**

## Limitations

There are things that can't be defined with annotations:

- values (instead of classes)
- mapping interfaces to implementations
- defining entries with an anonymous function

For that, you can combine annotations with [definitions in PHP](php-definitions.md).
