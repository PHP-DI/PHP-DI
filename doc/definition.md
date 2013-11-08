# Definitions

PHP-DI injects stuff into objects.

To **define** where and how to inject stuff, you have several options:

- let PHP-DI guess using [Reflection](http://www.php.net/manual/en/book.reflection.php)
- use annotations
- use PHP code (using `Container::set()`)
- use a PHP array
- use YAML files

You can also use several or all these options at the same time if you want to.

If you combine several sources, there are priorities that apply. From the highest priority to the least:

- Code definition (i.e. defined with `$container->set()`)
- File and array definitions (if A is added after B, then A prevails)
- Annotations
- Reflection

Read more in the [Definition overriding documentation](definition-overriding.md)


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

*Note*: importing annotations with `use DI\Annotation\Inject;` is optional since v3.5.

The `@Injectable` annotation let's you set options on injectable classes:

```php
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

For that, you can combine annotations with definitions in YAML files or PHP arrays (see below).


## PHP code

The container offers methods to quickly and easily define injections:

```php
$container = new Container();

// Values (not classes)
$container->set('db.host', 'localhost');
$container->set('db.port', 5000);

// Indexed non-empty array as value
$container->set('report.recipients', array(
	'bob@acme.example.com',
	'alice@acme.example.com'
));

// Direct mapping (not needed if you didn't disable Reflection)
$container->set('SomeClass');

// This is not recommended: will instantiate the class even when not used, prevents caching
$container->set('SomeClass', new SomeOtherClass(1, "hello"));

// Defines an instance of My\Class
$container->set('My\Class')
	->withConstructor(array('db.host', 'My\OtherClass'));

$container->set('My\OtherClass')
	->withScope(Scope::PROTOTYPE())
	->withConstructor(
		array(
			'host' => 'db.host',
			'port' => 'db.port',
		)
	)
	->withMethod('setFoo1', array('My\Foo1'))
	->withMethod('setFoo2', array('My\Foo1', 'My\Foo2'))
	->withMethod('setFoo3', array(
			'param1' => 'My\Foo1',
			'param2' => 'My\Foo2'
		))
	->withProperty('bar', 'My\Bar')
	->withProperty('baz', 'My\Baz', true);

// Mapping an interface to an implementation
$container->set('My\Interface')
	->bindTo('My\Implementation');

// Defining a named instance
$container->set('myNamedInstance')
	->bindTo('My\Class');

// Using an anonymous function
// not recommended: will not be cached
$container->set('My\Stuff', function(Container $c) {
								return new MyClass($c['db.host']);
							});
```


## PHP array

```php
$container->addDefinitions($array);
// or from a file
use DI\Definition\FileLoader\ArrayDefinitionFileLoader;
$container->addDefinitionsFromFile(new ArrayDefinitionFileLoader('config/di.php'));
```

You can also define injections with a PHP array.

Example of a `config/di.php` file (using [PHP 5.4 short arrays](http://php.net/manual/en/migration54.new-features.php)):

```php
<?php
return [

    // Values (not classes)
    'db.host' => 'localhost',
    'db.port' => 5000,

    // Indexed non-empty array as value
    'report.recipients' => [
        'bob@acme.example.com',
        'alice@acme.example.com'
    ],

    // Direct mapping (not needed if you didn't disable Reflection)
    'SomeClass' => [],

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
    ],

    // Defining a named instance
    'myNamedInstance' => [
        'class' => 'My\Class',
    ],

    // Using an anonymous function
    // not recommended: will prevent caching
    'My\Stuff' => function(Container $c) {
        return new MyClass($c['db.host']);
    ],

];
```


## YAML file

```php
use DI\Definition\FileLoader\YamlDefinitionFileLoader;
$container->addDefinitionsFromFile(new YamlDefinitionFileLoader('config/di.yml'));
```

Example of a `config/di.yml` file:

```yml
# Values (not classes)
db.host: localhost
db.port: 5000

# Indexed non-empty array as value
report.recipients:
    - bob@acme.example.com
    - alice@acme.example.com

# Direct mapping (not needed if you didn't disable Reflection)
SomeClass:

# Defines an instance of My\Class
My\Class:
  constructor: [db.host, My\OtherClass]

My\OtherClass:
  scope: prototype
  constructor:
    host: db.host
    port: db.port
  methods:
    setFoo1: My\Foo1
    setFoo2: [My\Foo1, My\Foo2]
    setFoo3:
      param1: My\Foo1
      param2: My\Foo2
    properties:
      bar: My\Bar
      baz:
        name: My\Baz
        lazy: true

# Mapping an interface to an implementation
My\Interface:
  class: My\Implementation

# Defining a named instance
myNamedInstance:
    class: My\Class
```

