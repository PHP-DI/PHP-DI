# Getting started


## Installation

Requires **PHP 5.3.0** or higher.

The easiest way is to install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md).

Create a file named `composer.json` in your project root:

```json
{
    "require": {
        "mnapoli/php-di": "3.2.*"
    }
}
```

Then, run the following commands:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

If you don't use Composer, you can directly [download](https://github.com/mnapoli/PHP-DI/tags) the sources and configure it with your autoloader.


## Usage


### 1: Define dependencies


You have to define a dependency graph between your objects, which we can represent like so (nodes are objects, links are dependencies):

![](graph.png)

PHP-DI offers several ways to define dependencies, so use which ones you like.

Below is a quick introduction to some options, but you can also read [the full documentation](definition.md).


#### Reflection

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

Of course, this is limited to constructor injection.

#### Annotations

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

See also the [complete documentation about annotations](definition.md).

#### PHP code

```php
$container = new Container();

// Values (not classes)
$container->set('db.host', 'localhost');
$container->set('db.port', 5000);

// Defines an instance of My\Class
$container->set('My\Class')
	->withConstructor(array('db.host', 'My\Interface'));

// Mapping an interface to an implementation
$container->set('My\Interface')
	->bindTo('My\Implementation');
```

#### PHP array

You can define injections with a PHP array too:

```php
<?php
return [

    // Values (not classes)
    'dbHost' => 'localhost',
    'dbPort' => 5000,

    // Class
    'My\Foo' => [
        'properties' => [
            'bar' => 'Bar',
        ],
        'methods' => [
            'setBaz' => 'Baz',
            'setValues' => ['dbHost', 'dbPort'],
        ],
    ],

];
```

See also the [complete documentation about array configuration](definition.md).

#### YAML file

You can define injections with a YAML file too:

```php
# Values (not classes)
dbHost: localhost
dbPort: 5000

# Class
My\Foo:
  properties:
    bar: Bar
  methods:
    setBaz: Baz
    setValues: [dbHost, dbPort]
```

See also the [complete documentation about YAML configuration](definition.md).


### 2: Get objects from the container

```php
$foo = $container->get('Foo');
```

But wait! Do not use this everywhere because this makes your code **dependent on the container**. This is an antipattern to dependency injection (it is like the service locator pattern: dependency *fetching* rather than *injection*).

So PHP-DI container should be called at the root of your application (in your Front Controller for example). To quote the Symfony docs about Dependency Injection:

> You will need to get [an object] from the container at some point but this should be as few times as possible at the entry point to your application.

For this reason, we are trying to provide integration with MVC frameworks (work in progress).

To sum up:

- If you can, use `$container->get()` in you root application class or front controller
- Else, use `$container->get()` in your controllers (but avoid it in your services) but keep in mind that your controllers will be dependent on the container

#### Zend Framework 1

If you are using ZF1, PHP-DI provides easy and clean integration so that you don't have to call the container (see above about the Service Locator pattern).

First, install the bridge to ZF1:

```json
{
    "require": {
        "mnapoli/php-di": "The version you want here",
        "mnapoli/php-di-zf1": "*"
    }
}
```

To use PHP-DI in your ZF1 application, you need to change the Dispatcher used by the Front Controller in the Bootstrap.

```php
    protected function _initDependencyInjection() {
		$container = new \DI\Container();

        $dispatcher = new \DI\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($container);

        Zend_Controller_Front::getInstance()->setDispatcher($dispatcher);
	}
```

**That's it!**

Now you have dependency injection **in your controllers**.

**Warning**: if you use Zend's autoloader (and not Composer), you will need to configure it:

```php
$autoloader->suppressNotFoundWarnings(true);
```

Read more on the [PHP-DI-ZF1 project on Github](https://github.com/mnapoli/PHP-DI-ZF1).
