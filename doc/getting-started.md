# Getting started

## Requirements

* **PHP 5.3.0** or higher

## Installation

### Install with Composer

The easiest way is to install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md).

First, create a file named `composer.json` in your project root:

```json
{
    "require": {
        "mnapoli/php-di": "1.1.*",
    }
}
```

Then, run the following commands:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

PHP-DI classes will be autoloaded by Composer if you use Composer's autoloading system (which I highly recommend).

## Usage

We can **declare** a dependency to be injected with the `@Inject` and `@var` annotations.

```php
<?php
use DI\Annotations\Inject;

class Class1 {
    /**
     * @Inject
     * @var Class2
     */
    private $class2;
```

However, declaring the dependency is not enough: the dependency needs to be injected:

```php
$class1 = new Class1();
\DI\Container::getInstance()->resolveDependencies($class1);
```

Where to call `resolveDependencies()`? Several solutions are possible:

- in your class constructors
- in the constructor of a base class (for your controllers for example)
- where your root application classes (controllers?) are instantiated

If your controller uses services who use repositories, then you just have to use `resolveDependencies()` on your controller when it is
created. The dependencies will have *their* dependencies injected too, etc... The dependency injection process is transitive:
repositories will be injected in services who will be injected in the controller.

## Zend Framework 1 integration

Are you using Zend Framework 1? Check out the ZF quickstart with Dependency Injection already configured: [zf-quickstart-di]
(https://github.com/mnapoli/zf-quickstart-di).

**Short version**: just change the base class you use for your controllers.

```php
<?php
class IndexController extends \DI\Zend\Controller\Action
```

This base controller class will inject the dependencies automatically in your controller and in every dependency that is injected.

## Configuration file

**The configuration file is optional**, PHP-DI will work with default behavior without it.

Read more about the [configuration file](doc/configuration-file)
