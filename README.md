The aim of PHP-DI is to make [*Dependency Injection*](http://en.wikipedia.org/wiki/Dependency_injection)
as simple as possible with PHP.

Unlike Flow3, Zend\DI, Symfony Service Container or Pimple (though they are of a great inspiration), PHP-DI:

* can be used by a monkey
* is non-intrusive and compatible with any framework
* is not limited to Services (anything can be injected)
* uses annotations for code-readability and ease of use
* can find and instantiate dependencies automatically without configuration


## Features

* Simple, yet full-featured
* Uses **annotations** for simplicity, readability and auto-completion in your IDE
* Automatic dependency resolution: you don't have to declare all your beans in configuration files
* Optional **lazy-loading** of dependencies
* **Cacheable** for optimal performances
* Class aliases (interface-implementation mapping)
* Easy installation with [**Composer**](http://getcomposer.org/doc/00-intro.md)
and easy integration with **Zend Framework** (see [Getting started](doc/getting-started))
* **Non-intrusive**: you can add PHP-DI into an existing project and use it *without impacting existing code*


## What is dependency injection, and why use it?

Read the [introduction to dependency injection with an example](doc/example).


## Short example

If you use dependency injection through your constructors:

```php
class Foo {
    private $bar;

    public function __construct(Bar $bar) {
        return $this->bar = $bar;
    }
}
```

PHP-DI will help you get an instance of `Foo`: it will create an instance of `Bar` and feed it through the constructor.

```php
$foo = DI\Container::get('Foo');
```

**No configuration needed**. That's as easy as possible!


## Practical annotations

PHP-DI provides useful annotations for Setter and Property injection:

```php
use DI\Annotations\Inject;

class Foo {
    /**
     * @Inject
     * @var Bar
     */
    private $bar;

    public function __construct() {
    	// The dependency is injected
        return $this->bar->sayHello();
    }
}
```

In this example, a instance of the `Bar` class is created and injected in the `Foo` class. **No configuration needed**.

*Note*: Of course, in the spirit of Dependency Injection, `Bar` will rather be an interface, and you will configure
which implementation will be injected through [configuration](doc/configure).


## More

Do you want more? PHP-DI comes on top of a classic container:

```php
$container = DI\Container::getInstance();

$container['dbAdapter'] = $myDbAdapter;
// or
$container->set('dbAdapter', $myDbAdapter);

$myDbAdapter = $container['dbAdapter'];
// or
$myDbAdapter = $container->get('dbAdapter');
```

## Even more

A more complete version of the previous example:

```php
$container = DI\Container::getInstance();
$container['db.params'] = [
	'dbname'   => 'foo',
	'user'     => 'root',
	'password' => '',
];
$container['dbAdapter'] = function(Container $c) {
	return new MyDbAdapter($c['db.params']);
};
```

and later:

```php
class Foo {
    /**
     * @Inject("dbAdapter")
     */
    private $dbAdapter;

    public function foo() {
        return $this->dbAdapter->query("");
    }
}
```


## Contribute

[![Build Status](https://secure.travis-ci.org/mnapoli/PHP-DI.png)](http://travis-ci.org/mnapoli/PHP-DI)

* PHP-DI sources are [on Github](https://github.com/mnapoli/PHP-DI).
* Read the doc: [Contributing](CONTRIBUTING)

PHP-DI is license under the MIT License.
