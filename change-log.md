# Change log

## 3.0

* Setter injection
* Constructor injection
* Scopes: singleton (share the same instance of the class) or prototype (create a new instance each time it is fetched). Defined at class level.
* Configuration is reworked from scratch. Now every configuration backend can do 100% of the job.
* Provided configuration backends:
    * Annotations: @Inject, @Scope
    * PHP code
* As a consequence, annotations are not mandatory anymore, all functionalities can be used with or without annotations.
* Code now follows PSR1 and PSR2 coding styles

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
