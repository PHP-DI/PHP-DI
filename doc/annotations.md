---
layout: documentation
current_menu: annotations
---

# Annotations

**Since PHP 8, annotations are deprecated in favor of [PHP attributes](attributes.md).**

---

On top of [autowiring](autowiring.md) and [PHP configuration files](php-definitions.md), you can define injections using annotations.

Using annotations do not affect performances when [compiling the container](performances.md).

## Installation

Annotations **are disabled by default**. To be able to use them, you first need to install the [Doctrine Annotations](http://doctrine-common.readthedocs.org/en/latest/reference/annotations.html) library using Composer:

```
composer require doctrine/annotations
```

Then you need to [configure the `ContainerBuilder`](container-configuration.md) to use them:

```php
$containerBuilder->useAnnotations(true);
```

Annotations are written in PHP docblock comments. They are used by a lot of modern libraries and frameworks, like [Doctrine](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html), [Symfony](http://symfony.com/) and more.

## @Inject

`@Inject` lets you define where PHP-DI should inject something, and optionally what it should inject.

It can be used on:

- the constructor (constructor injection)
- methods (setter/method injection)
- properties (property injection)

*Note: property injections occur after the constructor is executed, so any injectable property will be null inside `__construct`.*

**Since PHP-DI 7, `@Inject` will ignore types declared in phpdoc. Only types specified in PHP code will be considered.**

Here is an example of all possible uses of the `@Inject` annotation:

```php
class Example
{
    /**
     * Annotation combined with a type on the property:
     *
     * @Inject
     */
    private Foo $property1;

    /**
     * Explicit definition of the entry to inject:
     *
     * @Inject("db.host")
     */
    private $property2;

    /**
     * Annotation specifying exactly what to inject:
     *
     * @Inject({"db.host", "db.name"})
     */
    public function __construct($param1, $param2)
    {
    }

    /**
     * Annotation combined with PHP types:
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

    /**
     * Explicit definition of parameters by their name
     * (types are used for the other parameters):
     *
     * @Inject({"param2" = "db.host"})
     */
    public function method3(Foo $param1, $param2)
    {
    }
}
```

*Note: importing annotations with `use DI\Annotation\Inject;` is optional.*

### Troubleshooting @Inject

- you must use double quotes (`"`) instead of single quotes(`'`), for example: `@Inject("foo")`
- what's inside `@Inject()` must be in quotes, even if it's a class name: `@Inject("Acme\Blog\ArticleRepository")`
- `@Inject` is not meant to be used on the method to call with [`Container::call()`](container.md#call) (it will be ignored)
- **Since PHP-DI 7, `@Inject` will ignore types declared in phpdoc. Only types specified in PHP code will be considered.**

Note that `@Inject` is implicit on all constructors.

## Injectable

The `@Injectable` annotation lets you set options on injectable classes:

```php
/**
 * @Injectable(lazy=true)
 */
class Example
{
}
```

**The `@Injectable` annotation is optional: by default, all classes are injectable.**

## Limitations

There are things that can't be defined with annotations:

- values (instead of classes)
- mapping interfaces to implementations
- defining entries with an anonymous function

For that, you can combine annotations with [definitions in PHP](php-definitions.md).

## Troubleshooting

Since annotations are in PHP docblocks, the opcache option `opcache.save_comments` must be set to `1`. If it is set to `0`, comments will be stripped from the source code and annotations will not work.

The default value for this option is `1` so everything should work by default.

To check the value of this option, you can run the following command:

```
$ php -i | grep "opcache.save_comments"
```

Furthermore, please mind that annotations are case-sensitive. You should write `@Inject` and `@Injectable` instead of `@inject` and `@injectable` to avoid bugs on certain systems.
