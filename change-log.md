# Change log

## 2.0

Compatibility break with 1.x.

* Merge `@Value` annotation with `@Inject`: no difference between value and bean injection anymore
* Container implements ArrayAccess for get() and set() (`$container['db.host'] = 'localhost';`)
* Ini configuration files removed: configuration is done in PHP
* Allow to define beans within closures for lazy-loading
* Switched to MIT License

## 1.1

* Caching of annotations based on Doctrine caches

## 1.0

* DependencyManager renamed to Container
* Refactored basic Container usage with `get` and `set`
* Allow named injection `@Inject(name="")`
* Zend Framework integration
