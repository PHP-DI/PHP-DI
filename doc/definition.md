# Definitions

PHP-DI injects stuff into objects.

To **define** where and how to inject stuff, you have several options:

- let PHP-DI guess using [Reflection](http://www.php.net/manual/en/book.reflection.php)
- use annotations
- use a PHP array

You can also combine these definitions if you want to.



## Reflection

```php
$container->useReflection(true);
```

**Note: Reflection is enabled by default**

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

So use Reflection either:

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
use DI\Annotation\Inject;

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
     * @Inject(name="dbAdapter", lazy=true)
     */
    protected $property3;

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

    /**
     * @Inject({"param2" = "bar"})
     * @param Foo    $param1
     * @param string $param2
     */
    public function method3(Foo $param1, $param2) {
    }
}
```

The `@Injectable` annotation let's you set options on injectable classes:

```php
use DI\Annotation\Injectable;

/**
 * @Injectable(scope="prototype")
 */
class Example {
}
```

**The `@Injectable` annotation is optional: by default, all classes are injectable.**

There are still things that can't be defined with annotations:

- values (instead of classes)
- mapping interfaces to implementations
- defining entries with an anonymous function

For that, see below (and don't forget you can use reflection, annotations and PHP arrays at the same time).


## PHP array

```php
$container->addDefinitions($array);
// or
$container->addDefinitionsFromFile('config/di.php');
```

You can also define injections with a PHP array.

Example of a `config/di.php` file (using [PHP 5.4 short arrays](http://php.net/manual/en/migration54.new-features.php)):

```php
<?php
return [

    // Values (not classes)
    'db.host' => 'localhost',
    'db.port' => 5000,

    // Direct mapping (not needed if you enabled Reflection)
    'SomeClass' => 'SomeClass',

    // This is not recommended: will instantiate the class even when not used, prevents caching
    'SomeOtherClass' => new SomeOtherClass(1, "hello"),

    // Defines an instance of My\Class
    'My\Class' => [
        'constructor' => ['db.host', 'My\OtherClass'],
    ],

    'My\OtherClass' => [
        'scope' => Scope::PROTOTYPE(),
        'constructor' => [
            'host' => 'db.host',
            'port' => 'db.port',
        ],
        'methods' => [
            'setFoo1' => 'My\Foo1',
            'setFoo2' => ['My\Foo1', 'My\Foo2'],
            'setFoo3' => [
                'param1' => 'My\Foo1',
                'param2' => 'My\Foo2',
            ],
        ],
        'properties' => [
            'bar' => 'My\Bar',
            'baz' => [
                'name' => 'My\Baz',
                'lazy' => true,
            ],
        ],
    ],

    // Mapping an interface to an implementation
    'My\Interface' => [
        'class' => 'My\Implementation',
        'scope' => Scope::SINGLETON(),
    ],

    // Defining a named instance
    'myNamedInstance' => [
        // Using an anonymous function
        // not recommended: will not be cached
        'function' => function(Container $c) {
            return new MyClass($c['db.host']);
        },
    ],

    // Using an anonymous function
    // not recommended: will prevent caching
    'My\Stuff' => function(Container $c) {
        return new MyClass($c['db.host']);
    ],

];
```
