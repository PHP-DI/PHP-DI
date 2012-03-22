# PHP-DI - Simple PHP dependency injection library
by Matthieu Napoli

* Project home [http://github.com/mnapoli/PHP-DI](http://github.com/mnapoli/PHP-DI)
* Documentation [http://github.com/mnapoli/PHP-DI/wiki](http://github.com/mnapoli/PHP-DI/wiki)

### Introduction

The aim of this library is to make [Dependency Injection]
(http://en.wikipedia.org/wiki/Dependency_injection)
as simple as possible with PHP.

No fancy features, but no overhead. The simpler the better.

### Basic example

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

### Annotation usage

There are several alternative to inject:

        /**
         * @Inject
         * @var Class1
         */
        private $class1;

        /**
         * @Inject("Class2")
         */
        private $class2;

        /**
         * @Inject("Class3")
         * @var Class3Interface
         */
        private $class3Interface;

Each one has its own advantages (short, code completion, specification of
the interface and the implementation to use...). You choose your favorite.

### How are instances created?

A factory is used to create the instances that are injected.

By default, the strategy used is the Singleton pattern, which means that only one
instance of each class is instantiated.

This can be configured to a different strategy, or even to use a different factory.

In the near future, more configurations (via annotations) will be available very easily.

### Requirements

* __PHP 5.3__ or higher
* Using an autoloading system (as in most of the major frameworks)