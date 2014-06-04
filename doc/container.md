---
template: documentation
tab: container
---

# Using the container

This documentation describes the API of the container.

## get() & has()

The container implements the standard [ContainerInterop](https://github.com/container-interop/container-interop).
That means it implements `Interop\Container\ContainerInterface`:

```php
namespace Interop\Container;

interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     * @return mixed Entry.
     */
    public function get($id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     *
     * @param string $id Identifier of the entry to look for.
     * @return boolean
     */
    public function has($id);
}
```

If you type-hint against this interface instead of the implementation (`DI\Container`),
that means you will be able to use another container (compatible with ContainerInterop)
later.

## set()

Of course you can set entries on the container:

```php
$container->set('foo', 'bar');
```

However it is recommended to use definition files.
See the [definition documentation](definition.md).

## injectOn()

Sometimes you want to inject dependencies on an object that is already created.

For example, some old frameworks don't allow you to control how controllers are created.
With `injectOn`, you can ask the container to fulfill the dependencies after the object is created.

Example:

```php
class UserController extends BaseController
{
    /**
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

## make()

The container also offers a `make` method. This method is defined in the `DI\FactoryInterface`:

```php
interface FactoryInterface
{
    /**
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @param string $name       Entry name or a class name.
     * @param array  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                           parameters to specific values. Parameters not defined in this array will
     *                           be automatically resolved.
     *
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException   No entry or class found for the given name.
     * @return mixed
     */
    public function make($name, array $parameters = array());
}
```

The `make()` method works the same as `get()`, except it will create a new instance each time.
It will use the parameters provided for the constructor, and the missing parameters will be
resolved from the container.

It is very useful to create objects that do not belong *inside* the container (i.e. that are not services),
but that may have dependencies. It is also useful if you want to override some parameters in the constructor.
For example controllers, models, …

If you need to use the `make()` method inside a service, or a controller, or whatever, it is
recommended you type-hint against `FactoryInterface`. That avoids coupling your code to the container.
