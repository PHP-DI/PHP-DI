# PHP-DI - Simple PHP dependency injection library
by Matthieu Napoli

[![Build Status](https://secure.travis-ci.org/mnapoli/PHP-DI.png)](http://travis-ci.org/mnapoli/PHP-DI)

* *Project home* [http://mnapoli.github.com/PHP-DI/](http://mnapoli.github.com/PHP-DI/)

### Introduction

The aim of this library is to make [Dependency Injection]
(http://en.wikipedia.org/wiki/Dependency_injection)
as simple as possible with PHP.

No fancy features, but no overhead. The simpler the better.

#### Pros

* Annotations! No configuration file needed, easy to read and to write
* As little configuration as possible
* Doesn't need getters/setters
* Doesn't need any change to your existing code (you can give it a shot easily)

#### Cons

* You have to write a line of code in the constructor of your classes
(i'm looking for a solution about that)

### Why?

Using the singleton design pattern may be practical at first, but it comes with several disadvantages,
the main one being that it's not testable.

By using dependency injection, you can develop using contracts and not care what implementation
will be used. As the dependency can be injected by the "user", your class can be tested with mocks.

### Basic example

Say you have this class:

    class Class2 {
    }

An instance of Class2 can be automatically injected in another class very simply:

    use DI\Annotations\Inject;

    class Class1 {
        /**
         * @Inject
         * @var Class2
         */
        private $class2;

        public function __construct() {
            \DI\DependencyManager::getInstance()->resolveDependencies($this);
        }
    }

### Using interfaces or abstract types?

If you have something like:

    class Class1 {
		/**
		 * @Inject
		 * @var MyInterface
		 */
		private $myProperty;

and:

    class MyInterface {
    }
	class TheImplementationToUse implements MyInterface {
	}

PHP-DI will fail to inject "myProperty" because the type is an interface (MyInterface).

You have to do the mapping between the interface (or abstract class) and the implementation to use.
This can be done with a configuration file (di.ini):

	; Type mapping for injection
	di.implementation.map["MyInterface"] = "TheImplementationToUse"

And in your code (Bootstrap for example):

	DependencyManager::getInstance()->addConfigurationFile('di.ini');

### How are instances created?

A factory is used to create the instances that are injected.

By default, the strategy used is the Singleton pattern (`\DI\Factory\SingletonFactory`),
which means that only one
instance of each class/dependency is instantiated.

This can be configured to a different factory, using code or the configuration file (see below).
For example the `\DI\Factory\NewFactory`
will create a new instance each time a dependency is resolved.


### Installation

#### Requirements

* __PHP 5.3__ or higher

#### Install and use with Composer

The easiest way is to install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md):

    $ curl -s http://getcomposer.org/installer | php
	$ php composer.phar install

Then you have nothing to do, PHP-DI will be autoloaded by Composer
(if you use Composer's autoloading system, which I highly recommend).

#### Zend Framework

Are you using Zend Framework? Check out the official quickstart with
Dependency Injection already configured: [zf-quickstart-di](https://github.com/mnapoli/zf-quickstart-di)

#### Configuration file

The configuration file is optional, PHP-DI will work with default behavior without it.

Here is an example of a configuration file:

```
; PHP-DI - Dependency injection configuration

; The factory to use is the Singleton factory (default)
di.factory = "\DI\Factory\SingletonFactory"

; Type mapping for injection using abstract types
di.implementation.map["\My\Interface"] = "\My\Implementation"
di.implementation.map["\My\AbstractClass"] = "\My\OtherImplementation"
```

To import the configuration file:

```
DependencyManager::getInstance()->addConfigurationFile('di.ini');
```


### Projects using

Public projects using PHP-DI:
* [phpBeanstalkdAdmin](http://mnapoli.github.com/phpBeanstalkdAdmin/)


### Contribute

To run the project, get [composer](http://getcomposer.org/doc/00-intro.md):

    $ curl -s http://getcomposer.org/installer | php
	$ php composer.phar install
