# Change log

## 3.4

* You can now define arrays of values (in YAML, PHP, …) [#106](https://github.com/mnapoli/PHP-DI/pull/106/)
* FIXED [#100](https://github.com/mnapoli/PHP-DI/issues/100): bug for lazy injection in constructors

## 3.3

Read the [news entry](news/03-php-di-3-3.md).

* Inject dependencies on an existing instance with `Container::injectOn` (work from [Jeff Flitton](https://github.com/jflitton): [#89](https://github.com/mnapoli/PHP-DI/pull/89)).
* [#86](https://github.com/mnapoli/PHP-DI/issues/86): Optimized definition lookup (faster)
* FIXED [#87](https://github.com/mnapoli/PHP-DI/issues/87): Rare bug in the `PhpDocParser`, fixed by [drdamour](https://github.com/drdamour)

## 3.2

Read the [news entry](news/02-php-di-3-2.md).

Small BC-break: PHP-DI 3.0 and 3.1 injected properties before calling the constructor. This was confusing and [not supported for internal classes](https://github.com/mnapoli/PHP-DI/issues/74).
From 3.2 and on, properties are injected after calling the constructor.

* **[Lazy injection](doc/lazy-injection.md)**: it is now possible to use lazy injection on properties and methods (setters and constructors).
* Lazy dependencies are now proxies that extend the class they proxy, so type-hinting works.
* Addition of the **`ContainerBuilder`** object, that helps to [create and configure a `Container`](doc/container-configuration.md).
* Some methods for configuring the Container have gone **deprecated** in favor of the `ContainerBuilder`. Fear not, these deprecated methods will remain until next major version (4.0).
    * `Container::useReflection`, use ContainerBuilder::useReflection instead
    * `Container::useAnnotations`, use ContainerBuilder::useAnnotations instead
    * `Container::setDefinitionCache`, use ContainerBuilder::setDefinitionCache instead
    * `Container::setDefinitionsValidation`, use ContainerBuilder::setDefinitionsValidation instead
* The container is now auto-registered (as 'DI\Container'). You can now inject the container without registering it.

## 3.1.1

* Value definitions (`$container->set('foo', 80)`) are not cached anymore
* FIXED [#82](https://github.com/mnapoli/PHP-DI/issues/82): Serialization error when using a cache

## 3.1

Read the [news entry](news/01-php-di-3-1.md).

* Zend Framework 1 integration through the [PHP-DI-ZF1 project](https://github.com/mnapoli/PHP-DI-ZF1)
* Fixed the order of priorities when you mix different definition sources (reflection, annotations, files, …). See [Definition overriding](doc/definition-overriding.md)
* Now possible to define null values with  `$container->set('foo', null)` (see [#79](https://github.com/mnapoli/PHP-DI/issues/79)).
* Deprecated usage of `ContainerSingleton`, will be removed in next major version (4.0)

## 3.0.6

* FIXED [#76](https://github.com/mnapoli/PHP-DI/issues/76): Definition conflict when setting a closure for a class name

## 3.0.5

* FIXED [#70](https://github.com/mnapoli/PHP-DI/issues/70): Definition conflict when setting a value for a class name

## 3.0.4

* FIXED [#69](https://github.com/mnapoli/PHP-DI/issues/69): YamlDefinitionFileLoader crashes if YAML file is empty

## 3.0.3

* Fixed over-restrictive dependencies in composer.json

## 3.0.2

* [#64](https://github.com/mnapoli/PHP-DI/issues/64): Non PHP-DI exceptions are not captured-rethrown anymore when injecting dependencies (cleaner stack trace)

## 3.0.1

* [#62](https://github.com/mnapoli/PHP-DI/issues/62): When using aliases, definitions are now merged

## 3.0

Major compatibility breaks with 2.x.

* The container is no longer a Singleton (but `ContainerSingleton::getInstance()` is available for fools who like it)
* Setter injection
* Constructor injection
* Scopes: singleton (share the same instance of the class) or prototype (create a new instance each time it is fetched). Defined at class level.
* Configuration is reworked from scratch. Now every configuration backend can do 100% of the job.
* Provided configuration backends:
    * Reflection
    * Annotations: @Inject, @Injectable
    * PHP code (`Container::set()`)
    * PHP array
    * YAML file
* As a consequence, annotations are not mandatory anymore, all functionalities can be used with or without annotations.
* Renamed `DI\Annotations\` to `DI\Annotation\`
* `Container` no longer implements ArrayAccess, use only `$container->get($key)` now
* ZF1 integration broken and removed (work in progress for next releases)
* Code now follows PSR1 and PSR2 coding styles
* FIXED: [#58](https://github.com/mnapoli/PHP-DI/issues/58) Getting a proxy of an alias didn't work

## 2.1

* `use` statements to import classes from other namespaces are now taken into account with the `@var` annotation
* Updated and lightened the dependencies : `doctrine/common` has been replaced with more specific `doctrine/annotations` and `doctrine/cache`

## 2.0

Major compatibility breaks with 1.x.

* `Container::resolveDependencies()` has been renamed to `Container::injectAll()`
* Dependencies are now injected **before** the constructor is called, and thus are available in the constructor
* Merged `@Value` annotation with `@Inject`: no difference between value and bean injection anymore
* Container implements ArrayAccess for get() and set() (`$container['db.host'] = 'localhost';`)
* Ini configuration files removed: configuration is done in PHP
* Allow to define beans within closures for lazy-loading
* Switched to MIT License

Warning:

* If you use PHP 5.3 and __wakeup() methods, they will be called when PHP-DI creates new instances of those classes.

## 1.1

* Caching of annotations based on Doctrine caches

## 1.0

* DependencyManager renamed to Container
* Refactored basic Container usage with `get` and `set`
* Allow named injection `@Inject(name="")`
* Zend Framework integration
