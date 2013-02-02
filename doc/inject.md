# Inject


PHP-DI Container can contain and provide:

- beans (instances of classes)
- values (configuration values, like integers, strings, arrays, ...)


## @Inject annotation

We can declare a dependency with the `@Inject` annotation.

### Setter injection:

```php
use DI\Annotations\Inject;
use My\GreatClass;

class MyService {
    /**
     * @Inject
     */
    public function setMyDependency(GreatClass $myDependency) {
    	// ...
    }
}
```

PHP-DI will guess that you want a `GreatClass` instance based on the parameter type (`GreatClass $myDependency`).

### Property injection:

```php
use DI\Annotations\Inject;
use My\GreatClass;

class MyService {
    /**
     * @Inject
     * @var GreatClass
     */
    private $myDependency;

    // ...
}
```

Notice that the `@var` annotation is needed in this case because you can't specify a property's type.

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
    public function setDbHost($dbHost) {
    	// ...
    }
}
```

or:

```php
use DI\Annotations\Inject;

class MyService {
    /**
     * @Inject("db.host")
     */
    private $dbHost;

    // ...
}
```


### Named injection

While you can automatically inject an instance based on its type (using `@var` annotations or type-hinting),
you can also inject a *specific* instance:

```php
$container->set('myDependency', $myObject);
```

(to put something into the container, read the [configuration manual](doc/configure))

and then:

```php
use DI\Annotations\Inject;

class Class1 {
    /**
     * @Inject("myDependency")
     */
    public function setDependency($dependency) {
    	// ...
    }
}
```

or:

```php
use DI\Annotations\Inject;

class Class1 {
	/**
	 * @Inject("myDependency")
	 */
	private $dependency;
	
    // ...
}
```

Note that type-hinting or `@var` annotations are not required here.


### Lazy-loading dependencies

**Note: lazy-loading is only possible with property injection (not setter injection).**

If you are injecting multiple dependencies and not always using them, you might not want to load and
inject all the dependencies every time the class is used.

You can have a dependency to be loaded **only when it is used**:

```php
use DI\Annotations\Inject;

class Class1 {
    /**
     * @Inject(lazy=true)
     * @var Class2
     */
     private $class2;
	
    // ...
```

In this case, a Proxy class will be injected instead of the real `Class2` dependency.

The `Class2` instance will be loaded only when the property is used.

This can help save resources and improve performances.
