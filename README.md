PHP-DI is a Container that makes [*Dependency Injection*](http://en.wikipedia.org/wiki/Dependency_injection)
as practical as possible.

PHP-DI also tries to avoid falling into the trap of the "Service Locator" antipattern and help you do *real* dependency injection.


## Features

* Simple to start with
* Can be configured with **Reflection**, **PHP code** or **annotations** (each one is optional)
* You write the smallest configuration possible (thanks to reflection)
* **Performances**: supports a large number of Caches
* Can be used in an existing code base
* Offers lazy injection: lazy-loading of dependencies
* Integrates well into existing code


## What is dependency injection, and why use PHP-DI

Read the [introduction to dependency injection with an example](doc/example.md).

Dependency injection and DI containers are separate notions, and one should use of a container only if it makes things more practical (which is not always the case depending on the container you use).

PHP-DI is about this: make dependency injection more practical.

### How classic PHP code works

Here is how a code **not** using DI will roughly work:

* Application needs Foo (e.g. a controller), so:
* Application creates Foo
* Application calls Foo
    * Foo needs Bar (e.g. a service), so:
    * Foo creates Bar
    * Foo calls Bar
        * Bar needs Bim (a service, a repository, …), so:
        * Bar creates Bim
        * Bar does something

### How Dependency Injection works

Here is how a code using DI will roughly work:

* Application needs Foo, which needs Bar, which needs Bim, so:
* Application creates Bim
* Application creates Bar and gives it Bim
* Application creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

This is the pattern of **Inversion of Control**. The control of the dependencies is **inversed** from one being called to the one calling.

The main advantage: the one at the end of the caller chain is always **you**. So you can control every dependencies: you have a complete control on how your application works. You can replace a dependency by another (one you made for example).

For example that wouldn't be so easy if Library X uses Logger Y and you have to change the code of Library X to make it use your logger Z.

### How code using PHP-DI works

Now how does a code using PHP-DI works:

* Application needs Foo so:
* Application gets Foo from the Container, so:
    * Container creates Bim
    * Container creates Bar and gives it Bim
    * Container creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

In short, PHP-DI takes away all the work of creating and injecting dependencies.


## Usage

### Reflection

PHP-DI can use [PHP Reflection](http://fr.php.net/manual/fr/book.reflection.php) to understand what parameters a constructor needs:

```php
class Foo {
    private $bar;

    public function __construct(Bar $bar) {
        return $this->bar = $bar;
    }
}
```

PHP-DI will know that it should inject an instance of the `Bar` interface or class.

**No configuration needed!**

### Annotations

You can also use annotations to define injections, here is a short example:

```php
use DI\Annotation\Inject;

class Foo {
    /**
     * @Inject
     * @var Bar
     */
    protected $bar;

    /**
     * @Inject
     */
    public function setBaz(Baz $bin) {
    }

    /**
     * @Inject({"dbHost", "dbPort"})
     */
    public function setValues($param1, $param2) {
    }
}
```

See also the [complete documentation about annotations](doc/configure.md).

### PHP configuration

You can define injections with a PHP array too:

```php
<?php
return [

    // Values (not classes)
    'dbHost' => 'localhost',
    'dbPort' => 5000,

    'Foo' => [
        'properties' => [
            'bar' => 'Bar',
        ],
        'methods' => [
            'setBaz' => 'Baz',
            'setValues' => ['dbHost', 'dbPort'],
        ],
    ],

    'My\Class' => function(Container $c) {
        return new My\Class($c['dbHost']);
    ],

];
```

See also the [complete documentation about array configuration](doc/configure.md).

### Getting started

1. Define injections (see above, use reflection, annotations or file configuration)

You will define a dependency graph between your objects, which we can represent like so (nodes are objects, links are dependencies):

![](doc/graph.png)

2. Get an object from the container:

```php
$foo = $container->get('Foo');

// or
$foo = $container['Foo'];
```

But wait! Do not use this everywhere because this makes your code **dependent on the container**. This is an antipattern to dependency injection (it is like the service locator pattern: dependency *fetching* rather than *injection*).

So PHP-DI container should be called at the root of your application (in your Front Controller for example). To quote the Symfony docs about Dependency Injection:

> You will need to get [an object] from the container at some point but this should be as few times as possible at the entry point to your application.

For this reason, we are trying to provide integration with MVC frameworks (work in progress).

To sum up:

- If you can, use `$container->get()` in you root application class or front controller
- Else, use `$container->get()` in your controllers (but avoid it in your services) but keep in mind that your controllers will be dependent on the container


## More

There is a [complete documentation](doc/) waiting for you.


## Contribute

[![Build Status](https://secure.travis-ci.org/mnapoli/PHP-DI.png)](http://travis-ci.org/mnapoli/PHP-DI)

Contributions to code and docs are welcomed!

* PHP-DI sources are [on Github](https://github.com/mnapoli/PHP-DI).
* Read the doc: [Contributing](CONTRIBUTING.md)

PHP-DI under the MIT License.
