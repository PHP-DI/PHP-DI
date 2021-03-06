---
layout: documentation
current_menu: attributes
---

# Attributes

On top of [autowiring](autowiring.md) and [PHP configuration files](php-definitions.md), you can define injections using PHP 8 attributes.

Using attributes do not affect performances when [compiling the container](performances.md). For a non-compiled container, the PHP reflection is used but the overhead is minimal.

## Setup

Enable attributes [via the `ContainerBuilder`](container-configuration.md):

```php
$containerBuilder->useAttributes(true);
```

## Inject

`#[Inject]` lets you define where PHP-DI should inject something, and optionally what it should inject.

It can be used on:

- the constructor (constructor injection)
- methods (setter/method injection)
- properties (property injection)

*Note: property injections occur after the constructor is executed, so any injectable property will be null inside `__construct`.*

**Note: `#[Inject]` ignores types declared in phpdoc. Only types specified in PHP code are considered.**

Here is an example of all possible uses of the `#[Inject]` attribute:

```php
use DI\Attribute\Inject;

class Example
{
    /**
     * Attribute combined with a type on the property:
     */
    #[Inject]
    private Foo $property1;

    /**
     * Explicit definition of the entry to inject:
     */
    #[Inject('db.host')]
    private $property2;

    /**
     * Alternative to the above:
     */
    #[Inject(name: 'db.host')]
    private $property3;

    /**
     * The constructor is of course always called, but the
     * #[Inject] attribute can be used on a parameter
     * to specify what to inject.
     */
    public function __construct(Foo $foo, #[Inject('db.host')] $dbHost)
    {
    }

    /**
     * #[Inject] tells PHP-DI to call the method.
     * By default, PHP-DI uses the PHP types to find the service to inject:
     */
    #[Inject]
    public function method1(Foo $param)
    {
    }

    /**
     * #[Inject] can be used at the parameter level to
     * specify what to inject.
     * Note: #[Inject] *must be place* on the function too.
     */
    #[Inject]
    public function method2(#[Inject('db.host')] $param)
    {
    }

    /**
     * Explicit definition of the entries to inject:
     */
    #[Inject(['db.host', 'db.name'])]
    public function method3($param1, $param2)
    {
    }

    /**
     * Explicit definition of parameters by their name
     * (types are used for the other parameters):
     */
    #[Inject(['param2' => 'db.host'])]
    public function method4(Foo $param1, $param2)
    {
    }
}
```

*Note: remember to import the attribute class via `use DI\Attribute\Inject;`.*

### Troubleshooting attributes

- remember to import the attribute class via `use DI\Attribute\Inject;`
- `#[Inject]` is not meant to be used on the method to call with [`Container::call()`](container.md#call) (it will be ignored)
- `#[Inject]` ignores types declared in phpdoc. Only types specified in PHP code are considered.

Note that `#[Inject]` is implicit on all constructors (because constructors must be called to create an object).

## Injectable

The `#[Injectable]` attribute lets you set options on injectable classes:

```php
use DI\Attribute\Injectable;

#[Injectable(lazy: true)]
class Example
{
}
```

**The `#[Injectable]` attribute is optional: by default, all classes are injectable.**

## Limitations

There are things that can't be defined with attributes:

- values (instead of classes)
- mapping interfaces to implementations
- defining entries with an anonymous function

For that, you can combine attributes with [definitions in PHP](php-definitions.md).
