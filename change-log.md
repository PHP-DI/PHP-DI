# Change log

## 3.1

Small BC-break: PHP-DI 3.0 injected properties before calling the constructor. This was confusing and not supported for internal classes. From 3.1 and on, properties are injected after calling the constructor.

* **Lazy injection**: it is now possible to use lazy injection on properties and methods (setters and constructors).
* Lazy dependencies are now proxies that extend the class they proxy, so type-hinting works.
* The container is now auto-registered (as 'DI\Container').
* `ContainerBuilder` object that helps to create and configure a `Container`

## 3.0.5

* FIXED[#69](https://github.com/mnapoli/PHP-DI/issues/69): Definition conflict when setting a value for a class name

## 3.0.4

* FIXED[#70](https://github.com/mnapoli/PHP-DI/issues/70): YamlDefinitionFileLoader crashes if YAML file is empty

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
