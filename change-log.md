# Change log

## 5.0

Improvements:

- Lighter package: requires 4 less Composer dependencies by default

BC breaks:

- [#198](https://github.com/mnapoli/PHP-DI/issues/198) `ocramius/proxy-manager` is not installed by default anymore, you need to require it in `composer.json` if you want to use **lazy injection**

## 4.4

Read the [news entry](news/13-php-di-4-4-released.md).

- [#185](https://github.com/mnapoli/PHP-DI/issues/185) Support for invokable objects in `Container::call()`
- [#192](https://github.com/mnapoli/PHP-DI/pull/192) Support for invokable classes in `Container::call()` (will instantiate the class)
- [#184](https://github.com/mnapoli/PHP-DI/pull/184) Option to ignore phpdoc errors

## 4.3

Read the [news entry](news/11-php-di-4-3-released.md).

- [#176](https://github.com/mnapoli/PHP-DI/pull/176) New definition type for reading environment variables: `DI\env()`
- [#181](https://github.com/mnapoli/PHP-DI/pull/181) `DI\FactoryInterface` and `DI\InvokerInterface` are now auto-registered inside the container so that you can inject them without any configuration needed
- [#173](https://github.com/mnapoli/PHP-DI/pull/173) `$container->call(['MyClass', 'method]);` will get `MyClass` from the container if `method()` is not a static method

## 4.2.2

- Fixed [#180](https://github.com/mnapoli/PHP-DI/pull/180): `Container::call()` with object methods (`[$object, 'method']`) is now supported

## 4.2.1

- Support for PHP 5.3.3, which was previously incomplete because of a bug in the reflection (there is now a workaround for this bug)

But if you can, seriously avoid this (really old) PHP version and upgrade.

## 4.2

Read the [news entry](news/10-php-di-4-2-released.md).

**Minor BC-break**: Optional parameters (that were not configured) were injected, they are now ignored, which is what naturally makes sense since they are optional.
Example:

```php
    public function __construct(Bar $bar = null)
    {
        $this->bar = $bar ?: $this->createDefaultBar();
    }
```

Before 4.2, PHP-DI would try to inject a `Bar` instance. From 4.2 and onwards, it will inject `null`.

Of course, you can still explicitly define an injection for the optional parameters and that will work.

All changes:

* [#162](https://github.com/mnapoli/PHP-DI/pull/162) Added `Container::call()` to call functions with dependency injection
* [#156](https://github.com/mnapoli/PHP-DI/issues/156) Wildcards (`*`) in definitions
* [#164](https://github.com/mnapoli/PHP-DI/issues/164) Prototype scope is now available for `factory()` definitions too
* FIXED [#168](https://github.com/mnapoli/PHP-DI/pull/168) `Container::has()` now returns false for interfaces and abstract classes that are not mapped in the definitions
* FIXED [#171](https://github.com/mnapoli/PHP-DI/issues/171) Optional parameters are now ignored (not injected) if not set in the definitions (see the BC-break warning above)

## 4.1

Read the [news entry](news/09-php-di-4-1-released.md).

BC-breaks: None.

* [#138](https://github.com/mnapoli/PHP-DI/issues/138) [Container-interop](https://github.com/container-interop/container-interop) compliance
* [#143](https://github.com/mnapoli/PHP-DI/issues/143) Much more explicit exception messages
* [#157](https://github.com/mnapoli/PHP-DI/issues/157) HHVM support
* [#158](https://github.com/mnapoli/PHP-DI/issues/158) Improved the documentation for [Symfony 2 integration](http://php-di.org/doc/frameworks/symfony2.html)

## 4.0

Major changes:

* The configuration format has changed ([read more here to understand why](news/06-php-di-4-0-new-definitions.md))

Read the migration guide if you are using 3.x: [Migration guide from 3.x to 4.0](doc/migration/4.0.md).

BC-breaks:

* YAML, XML and JSON definitions have been removed, and the PHP definition format has changed (see above)
* `ContainerSingleton` has been removed
* You cannot configure an injection as lazy anymore, you can only configure a container entry as lazy
* The Container constructor now takes mandatory parameters. Use the ContainerBuilder to create a Container.
* Removed `ContainerBuilder::setDefinitionsValidation()` (no definition validation anymore)
* `ContainerBuilder::useReflection()` is now named: `ContainerBuilder::useAutowiring()`
* `ContainerBuilder::addDefinitionsFromFile()` is now named: `ContainerBuilder::addDefinitions()`
* The `$proxy` parameter in `Container::get($name, $proxy = true)` hase been removed. To get a proxy, you now need to define an entry as "lazy".

Other changes:

* Added `ContainerInterface` and `FactoryInterface`, both implemented by the container.
* [#115](https://github.com/mnapoli/PHP-DI/issues/115) Added `Container::has()`
* [#142](https://github.com/mnapoli/PHP-DI/issues/142) Added `Container::make()` to resolve an entry
* [#127](https://github.com/mnapoli/PHP-DI/issues/127) Added support for cases where PHP-DI is wrapped by another container (like Acclimate): PHP-DI can now use the wrapping container to perform injections
* [#128](https://github.com/mnapoli/PHP-DI/issues/128) Configure entry aliases
* [#110](https://github.com/mnapoli/PHP-DI/issues/110) XML definitions are not supported anymore
* [#122](https://github.com/mnapoli/PHP-DI/issues/122) JSON definitions are not supported anymore
* `ContainerSingleton` has finally been removed
* Added `ContainerBuilder::buildDevContainer()` to get started with a default container very easily.
* [#99](https://github.com/mnapoli/PHP-DI/issues/99) Fixed "`@param` with PHP internal type throws exception"

## 3.5.1

* FIXED [#126](https://github.com/mnapoli/PHP-DI/issues/126): `Container::set` without effect if a value has already been set and retrieved

## 3.5

Read the [news entry](news/05-php-di-3-5.md).

* Importing `@Inject` and `@Injectable` annotations is now optional! It means that you don't have to write `use DI\Annotation\Inject` anymore
* FIXED [#124](https://github.com/mnapoli/PHP-DI/issues/124): `@Injects` annotation conflicts with other annotations

## 3.4

Read the [news entry](news/04-php-di-3-4.md).

* [#106](https://github.com/mnapoli/PHP-DI/pull/106) You can now define arrays of values (in YAML, PHP, …) thanks to [@unkind](https://github.com/unkind)
* [#98](https://github.com/mnapoli/PHP-DI/issues/98) `ContainerBuilder` is now fluent thanks to [@drdamour](https://github.com/drdamour)
* [#101](https://github.com/mnapoli/PHP-DI/pull/101) Optional parameters are now supported: if you don't define a value to inject, their default value will be used
* XML definitions have been deprecated, there weren't even documented and were not maintained. They will be removed in 4.0.
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
