---
template: documentation
tab: definition
---

# Definitions

PHP-DI injects stuff into objects.

To **define** where and how to inject stuff, you have several options:

- use autowiring: let PHP-DI guess using [Reflection](http://www.php.net/manual/en/book.reflection.php)
- use annotations
- use PHP configuration

You can also use several or all these options at the same time if you want to.

If you combine several sources, there are priorities that apply. From the highest priority to the least:

- Explicit definition on the container (i.e. defined with `$container->set()`)
- PHP file definitions (if you add several configuration files, then the last one can override entries from the previous ones)
- Annotations
- Autowiring

Read more in the [Definition overriding documentation](definition-overriding.md)


## Autowiring

```php
$container->useAutowiring(true);
```

**Note: autowiring is enabled by default**

This solution is the simplest, but also restricted.

Example:

```php
class Foo {
    public function __construct(Bar $param1) {
    }
}
```

When creating a new `Foo` instance, the constructor has to be called. So PHP-DI will look at the parameters and guess: *`$param1` must be an instance of `Bar`* (that's [type hinting](http://www.php.net/manual/en/language.oop5.typehinting.php)).

Simple! And it works!

However, PHP-DI won't be able to resolve cases like this:

```php
class Foo {
    public function __construct($param1, $param2) {
    }
    public function setStuff($stuff) {
    }
}
```

It will not know what parameters to give to the constructor, and `setStuff()` will not be called.

So use autowiring either:

- if you also use other definition options (annotations, file configuration…)
- if you only need constructor injection, and if you always use type-hinting


## Annotations

```php
$container->useAnnotations(true);
```

**Note: Annotations are enabled by default**

Annotations are written in PHP docblock comments. They are used by a lot of modern libraries and frameworks, like [Doctrine](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html), [Symfony](http://symfony.com/), [Flow](http://flow.typo3.org/), [PHPUnit](http://www.phpunit.de/manual/3.7/en/)…

`@Inject` let's you define where PHP-DI should inject stuff, and what should it inject. You can also use `@var` and `@param` PhpDoc tags to define what should be injected.

It can be used over:

- the constructor (constructor injection)
- setters (setter injection) or any method actually
- properties (property injection)

Here is an example of all possible uses of the `@Inject` annotation:

```php
class Example {
    /**
     * @Inject
     * @var Foo
     */
    protected $property1;
    /**
     * @Inject("db.host")
     */
    protected $property2;

    /**
     * @Inject
     * @param Foo $param1
     * @param Bar $param2
     */
    public function __construct($param1, $param2) {
    }

    /**
     * @Inject
     */
    public function method1(Foo $param) {
    }

    /**
     * @Inject({"db.host", "db.name"})
     */
    public function method2($param1, $param2) {
    }
}
```

*Note*: importing annotations with `use DI\Annotation\Inject;` is optional since v3.5.

The `@Injectable` annotation let's you set options on injectable classes:

```php
/**
 * @Injectable(scope="prototype", lazy=true)
 */
class Example {
}
```

**The `@Injectable` annotation is optional: by default, all classes are injectable.**

There are still things that can't be defined with annotations:

- values (instead of classes)
- mapping interfaces to implementations
- defining entries with an anonymous function

For that, you can combine annotations with definitions in PHP (see below).


## PHP configuration

You can define injections with a PHP array:

```php
$containerBuilder->addDefinitions('config.php');
```

This definition format is the most powerful of all, but also more verbose.
There are several kind of **entries** you can define:

- **value**: string, int, array, class instance, …
- **object**: you describe to the container *how* it will create an instance of a class
- **factory**: a `callable` (function) that will return the entry
- **alias**: alias an entry name to another entry name

PHP-DI provides function helpers for this (to define *values*, you don't need a function):

- `DI\object($classname = null)`: define an object entry
- `DI\factory($factory)`: define a factory that returns an entry
- `DI\link($entryName)`: used to define alias entries, and also to reference other entries in object definitions (see below)

Example of a `config.php` file (using [PHP 5.4 short arrays](http://php.net/manual/en/migration54.new-features.php)):

```php
<?php

return [

    // Values (not classes)
    'db.host'           => 'localhost',
    'db.port'           => 5000,
    'report.recipients' => [
        'bob@acme.example.com',
        'alice@acme.example.com'
    ],

    // Explicit definition of an object (unnecessary if you use autowiring)
    'SomeClass' => DI\object(),

    // Value definition of a class instance
    // Not recommended: will instantiate the class on every request, even when not used
    'SomeOtherClass' => new SomeOtherClass(1, "hello"),

    // Defines an instance of My\Class
    'My\Class' => DI\object()
        ->constructor(DI\link('db.host'), DI\link('My\OtherClass')),

    'My\OtherClass' => DI\object()
        ->scope(Scope::PROTOTYPE())
        ->constructor(DI\link('db.host'), DI\link('db.port'))
        ->method('setFoo2', DI\link('My\Foo1'), DI\link('My\Foo2'))
        ->property('bar', 'My\Bar'),

    // Define only specific parameters
    'My\AnotherClass' => DI\object()
        ->constructorParameter('someParam', 'value to inject')
        ->methodParameter('setFoo2', 'someParam', DI\link('My\Foo')),

    // Mapping an interface to an implementation
    'My\Interface' => DI\object('My\Implementation'),

    // Defining a named instance
    'myNamedInstance' => DI\object('My\Class'),

    // Using an anonymous function
    'My\Stuff' => DI\factory(function (Container $c) {
        return new MyClass($c->get('db.host'));
    }),

    // We can set the scope on the factory too
    // This will return a new object each time we request SomeOtherClass
    'SomeOtherClass' => DI\factory(function () {
        return new SomeOtherClass();
    })->scope(Scope::PROTOTYPE()),

    // Defining an alias to another entry
    'some.entry' => DI\link('some.other.entry'),

    // Defining a value based on an environment variable
    'db1.url' => DI\env('DATABASE_URL'),
    // With a default value
    'db2.url' => DI\env('DATABASE_URL', 'postgresql://user:pass@localhost/db'),
    // With a default value that is another entry
    'db2.host' => DI\env('DATABASE_HOST', DI\link('db.host')),

];
```

*Watch out:* remember the helper functions (`DI\object()` for example) are just namespaced functions, not classes.
Do not use `new` (e.g. `new DI\object()`) else you will get a fatal error "Class 'DI\object' not found".


In addition to defining entries in an array, you can set them directly in the container as shown below.
**Using an array is however recommended since it allows to cache the definitions.**

```php
// Value
$container->set('db.host', 'localhost');

// Object
$container->set('My\Class', \DI\object()
    ->constructor('some raw value'))
);

// ...
```

The API is the same as shown above for the PHP array containing definitions.

### Wildcards

You can use wildcards to define a batch of entries. It can be very useful to bind interfaces to implementations:

```php
return [
    'Blog\Domain\*RepositoryInterface' => DI\object('Blog\Architecture\*DoctrineRepository'),
];
```

In our example, the wildcard will match `Blog\Domain\UserRepositoryInterface`, and it will map it to
`Blog\Architecture\UserDoctrineRepository`.

Good to know:

- the wildcard does not match across namespaces
- an exact match (i.e. without `*`) will always be chosen over a match with a wildcard
(first PHP-DI looks for an exact match, then it searches in the wildcards)
- in case of "conflicts" (i.e. 2 different matches with wildcards), the first match will prevail
