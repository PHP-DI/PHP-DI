# Inject


PHP-DI Container can contain and provide:

- beans (instances of classes)
- values (configuration values, like integers, strings, arrays, ...)


## The container

The container is a singleton:

```php
$container = \DI\Container::getInstance();
```

To get something from the container, you can simply do:

```php
// Object style
$something = $container->get('something');
// Or array style
$something = $container['something'];
```

(to put something into the container, read the [configuration manual](doc/configure))

If you want to get class instances, you don't need any configuration, you can simply do:

```php
// Object style
$something = $container->get('My\GreatClass');
// Or array style
$something = $container['My\GreatClass'];
```

This will simply return an instance of the class `My\GreatClass`.


## @Inject annotation

If you are lazy (like we all are), you don't want to write that much code.
Also, if you want dependency injection, you'll have noticed by now that this looks more like
*Dependency Fetching* rather than *Dependency Injection*.

### Declaring

We can declare a dependency to inject with the `@Inject` and `@var` annotations.

Like the previous example, we can inject an instance of `My\GreatClass` right into our class:

```php
use DI\Annotations\Inject;
use My\GreatClass;

class MyService {
    /**
     * @Inject
     * @var GreatClass
     */
    private $myDependency;
}
```

*`@Inject` is an annotation defined by PHP-DI, `@var` is the standard phpDoc annotation*

### Injecting

Declaring the dependency with `@Inject` is not enough: the dependency needs to be injected by PHP-DI.

You can't use `new` to create a new instance of your class `MyService`. Instead use PHP-DI:

```php
// The best solution
$myService = $container->get('MyService'); // The dependencies will be injected before the constructor is called

// Another solution
$myService = new MyService(); // The dependencies will not be available in the constructor
$container->injectAll($myService);

// Or also
class MyService {
    public function __construct() {
        \DI\Container::getInstance()->injectAll($this); // The dependencies are available after this line
    }
```

Where to call `injectAll()`? Several solutions are possible:

- in your class constructors, or in the constructor of a base class (for your controllers for example)
- where your root application classes (front controller, routing?) are instantiated
- or use `$container->get()` instead and you don't need to call `injectAll()`
- ...

For example in the [Zend Framework 1.x integration](getting-started), the dependencies are injected
when the controller is created by Zend Framework.

If your controller uses services which use repositories, then you just have to use `injectAll()`
on your controller when it is created. **The dependency injection process is transitive**: repositories will be injected in services which
will be injected in the controller.


### Value injection

Like with `$container->get($something)`, you can of course inject values:

```php
use DI\Annotations\Inject;

class MyService {
    /**
     * @Inject("db.host")
     */
    private $dbHost;
}
```

(to put something into the container, read the [configuration manual](doc/configure))


### Named injection

While you can automatically inject an instance by specifying its type using the `@var` annotation,
you can also inject a *specific* instance:

```php
$container->set('myDependency', $myObject);
```

(to put something into the container, read the [configuration manual](doc/configure))

and then:

```php
<?php
class Class1 {
	/**
	 * @Inject("myDependency")
	 */
	private $dependency;
```


### Lazy-loading dependencies

With the default behavior, the dependencies are created when injected:

```php
use DI\Annotations\Inject;

class Class1 {
    /**
     * @Inject
     * @var Class2
     */
    private $class2;

    public function __construct() {
        \DI\Container::getInstance()->injectAll($this);
    }

    public function getSomething() {
        return $this->class2->getSomethingElse();
    }
}
```

If you are injecting multiple dependencies and not always using them, you might not want to load and
inject all the dependencies every time the class is used.

You can force a dependency to be loaded only when it is used:

```php
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
