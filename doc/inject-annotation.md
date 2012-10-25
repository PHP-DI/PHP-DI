# @Inject annotation

The `@Inject` annotation allows you to inject a dependency automatically.

Say you have a dependency (a service, a DAO, a Helper... any class):

```php
<?php
class Class2 {
}
```

An instance of `Class2` can be automatically injected in another class very simply:

```php
<?php
use DI\Annotations\Inject;

class Class1 {
    /**
     * @Inject
     * @var Class2
     */
    private $class2;

    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
    }
}
```

The line in the constructor will inject the dependencies, read the [getting started](doc/getting-started) guide to see how you can automate this.

## Named injection

While you can automatically inject an instance by specifying its type using the `@var` annotation, you can also inject a *specific* instance:

```php
$container = \DI\Container::getInstance();
$container->set('myDependency', $myObject);
```

and then:

```php
<?php
class NamedInjectionClass {
	/**
	 * @Inject(name="myDependency")
	 */
	private $dependency;
```

## Using interfaces or abstract types

If you have something like:

```php
<?php
class Class1 {
	/**
	 * @Inject
	 * @var MyInterface
	 */
	private $myProperty;
```

and:

```php
<?php
interface MyInterface {
}
class TheImplementationToUse implements MyInterface {
}
```

PHP-DI will fail to inject `myProperty` because the type is an interface (MyInterface) and it will not know what class to use.

You have to do the mapping between the interface (or abstract class) and the implementation to use.
This can be done in the [configuration file](doc/configuration-file):

```ini
; Type mapping for injection
di.types.map["MyInterface"] = "TheImplementationToUse"
```

## Lazy-loading dependencies

With the default behavior, the dependencies are injected in the class constructor:

```php
<?php
use DI\Annotations\Inject;

class Class1 {
    /**
     * @Inject
     * @var Class2
     */
    private $class2;

    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
    }

    public function getSomething() {
        return $this->class2->getSomethingElse();
    }
}
```

If you are injecting multiple dependencies and not always using them, you might not want to load and inject all the dependencies every time the class is used.

You can force a dependency to be loaded only when it is used:

```php
<?php
class Class1 {
    /**
     * @Inject(lazy=true)
     * @var Class2
     */
     private $class2;
```

In this case, when `Class1` is instantiated, a Proxy class will be injected instead of the real `Class2` dependency.

Only when the dependency is used (in `getSomething()`) the `Class2` instance will be loaded.

This can help save resources and improve performances.

*Warning: this feature is experimental, it's effectiveness in improving performances is still to be proved.*
