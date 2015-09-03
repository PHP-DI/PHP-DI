---
layout: documentation
---

# Using the container

This documentation describes the API of the container object itself.

## get() & has()

The container implements the [container-interop](https://github.com/container-interop/container-interop) standard. That means it implements [`Interop\Container\ContainerInterface`](https://github.com/container-interop/container-interop/blob/master/src/Interop/Container/ContainerInterface.php):

```php
namespace Interop\Container;

interface ContainerInterface
{
    public function get($id);
    public function has($id);
}
```

You are encouraged to type-hint against this interface instead of the implementation (`DI\Container`) whenever possible. Doing so means your code is decoupled from PHP-DI and you can switch to another container anytime.

## set()

You can set entries directly on the container:

```php
$container->set('foo', 'bar');
$container->set('MyInterface', \DI\object('MyClass'));
```

However it is recommended to use definition files. See the [definition documentation](definition.md).

## make()

The container also offers a `make()` method. This method is defined in [`DI\FactoryInterface`](https://github.com/PHP-DI/PHP-DI/blob/master/src/DI/FactoryInterface.php).

```php
class GithubProfile
{
    public function __construct(ApiClient $client, $user)
    ...
}

$container->make('GithubProfile', [
    'user' => 'torvalds',
]);
```

The `make()` method works like `get()` except *it will create a new instance every time it is called*.

It will use the parameters provided for the constructor, and the missing parameters will be
resolved from the container.

It is useful to create objects that do not belong *inside* the container (i.e. that are not services, or that are not [**stateless**](https://igor.io/2013/03/31/stateless-services.html)), but that have dependencies. It is also useful if you want to override some parameters in the constructor.

If you need to use the `make()` method inside a service, or a controller, or whatever, it is
recommended that you type-hint against `FactoryInterface`. That avoids coupling your code to the container.
`DI\FactoryInterface` is automatically bound to `DI\Container` so you can inject it without any configuration.

## call()

The container exposes a `call()` method that can invoke any PHP callable.

It offers the following additional features over using `call_user_func()`:

- named parameters (pass parameters indexed by name instead of position)

    ```php
    $container->call(function ($foo, $bar) {
        // ...
    }, [
        'param1' => 'Hello',
        'param2' => 'World',
    ]);

    // Can also be useful in a micro-framework for example
    $container->call($controller, $_GET + $_POST);
    ```

- dependency injection based on the type-hinting

    ```php
    $container->call(function (Logger $logger, EntityManager $em) {
        // ...
    });
    ```

- dependency injection based on explicit definition

    ```php
    $container->call(function ($dbHost) {
        // ...
    }, [
        // Either indexed by parameter names
        'dbHost' => \DI\get('db.host'),
    ]);

    $container->call(function ($dbHost) {
        // ...
    }, [
        // Or not indexed
        \DI\get('db.host'),
    ]);
    ```

The best part is that you can mix all that:

```php
$container->call(function (Logger $logger, $dbHost, $operation) {
    // ...
}, [
    'operation' => 'delete',
    'dbHost'    => \DI\get('db.host'),
]);
```

The `call()` method is particularly useful to invoke controllers, for example:

```php
$controller = function ($name, EntityManager $em) {
    // ...
}

$container->call($controller, $_GET); // $_GET contains ['name' => 'John']
```

This leaves the liberty to the developer writing controllers to get request parameters
*and* services using dependency injection.

As with `make()`, `call()` is defined in `DI\InvokerInterface` so that you can type-hint
against that interface without coupling yourself to the container.
`DI\InvokerInterface` is automatically bound to `DI\Container` so you can inject it without any configuration.

```php
namespace DI;

interface InvokerInterface
{
    public function call($callable, array $parameters = []);
}
```

`Container::call()` can call any callable, that means:

- closures
- functions
- object methods and static methods
- invokable objects (objects that implement [__invoke()](http://php.net/manual/en/language.oop5.magic.php#object.invoke))

Additionally you can call:

- name of [invokable](http://php.net/manual/en/language.oop5.magic.php#object.invoke) classes: `$container->call('My\CallableClass')`
- object methods (give the class name, not an object): `$container->call(['MyClass', 'someMethod'])`

In both case, `'My\CallableClass'` and `'MyClass'` will be resolved by the container using `$container->get()`.

That saves you from a more verbose form, for example:

```php
$object = $container->get('My\CallableClass');
$container->call($object);

// can be written as
$container->call('My\CallableClass');
```

## injectOn()

Sometimes you want to inject dependencies on an object that is already created.

For example, some old frameworks don't allow you to control how controllers are created.
With `injectOn`, you can ask the container to fulfill the dependencies after the object is created.

Keep in mind it's usually always better to use `get()` or `make()` instead of `injectOn()`,
use it only where you really have to.

Example:

```php
class UserController extends BaseController
{
    /**
     * @Inject
     * @var SomeService
     */
    private $someService;

    public function __construct()
    {
        // The framework doesn't let us control how the controller is created, so
        // we can't use the container to create the controller
        // So we ask the container to inject dependencies
        $container->injectOn($this);

        // Now the dependencies are injected
        $this->someService->doSomething();
    }
}
```

As you might have guessed, you can't use constructor injection with this method.
But other kind of injections (property or setter) will work, whether you use annotations
or whether you configured your object in a definition file.
