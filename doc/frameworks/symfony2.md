---
layout: documentation
---

# PHP-DI in Symfony 2

If you are using Symfony 2, PHP-DI provides easy and clean integration, without replacing the original container
(so all bundles and existing code still work).

Just to be clear: PHP-DI will work alongside Symfony's container. So you can use both at the same time.

## Installation

First, install the bridge:

```json
{
    "require": {
        "mnapoli/php-di": "The version you want here",
        "mnapoli/php-di-symfony2": "*"
    }
}
```

Now you need to configure Symfony to use the alternative container in your `AppKernel`:

```php
class AppKernel extends Kernel
{
    /**
     * Gets the container's base class.
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return 'DI\Bridge\Symfony\SymfonyContainerBridge';
    }

    /**
     * Initializes the DI container.
     */
    protected function initializeContainer()
    {
        parent::initializeContainer();

        // Configure your container here
        // http://php-di.org/doc/container-configuration
        $builder = new \DI\ContainerBuilder();
        $builder->wrapContainer($this->getContainer());

        $this->getContainer()->setFallbackContainer($builder->build());
    }
}
```


## Usage

You can now define [controllers as services](http://symfony.com/doc/current/cookbook/controller/service.html),
without any configuration, using PHP-DI's power!

Example for the routing configuration:

```yaml
my_route:
    pattern:  /product-stock/clear
    defaults: { _controller: MyBundle\Controller\ProductController:clearAction }
```

**Careful! Note that you need to use `:` as a separator, not `::` (else it will not go through the container).**
The `:` notation means `service_id:method`, whereas the `::` notation means `class::method`.

Example with constructor injection:

```php
class ProductController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function clearAction()
    {
        $this->productService->clearStock();
    }
}
```

Example with property injection:

```php
class ProductController
{
    /**
     * @Inject
     * @var ProductService
     */
    private $productService;

    public function clearAction()
    {
        $this->productService->clearStock();
    }
}
```

### Routing annotations

It is possible to use [annotations for routing](http://richardmiller.co.uk/2011/10/25/symfony2-routing-to-controller-as-service-with-annotations/) and still have the controller created by PHP-DI.

You achieve that by specifying the service ID in the `@Route` annotation (which is most probably the class name itself unless you have a custom setup):

```php
/**
 * @Route(service="My\TestController")
 */
class TestController extends Controller
{
    private $dependency;

    public function __construct(SomeDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * @Route("test")
     */
    public function testAction()
    {
        return new Response('ok');
    }
}
```


## Using Symfony's services in PHP-DI

Let's say you want to inject the `EntityManager` in your controller: the entity manager is defined
in Symfony's container, and the controller is resolved by PHP-DI.

**You can reference services that are in Symfony's container in PHP-DI's configuration**.
That's because PHP-DI is designed to play nice with others:

```php
return [
    'Acme\MyBundle\Controller\ProductController' => DI\object()
        ->constructor(DI\get('doctrine.orm.entity_manager')),
];
```

However Symfony was not designed to play with his friends, so it has no idea that there is
another container in the application. **You can't inject PHP-DI's services in a Symfony service**.
That's however rarely a problem.

### Service name aliases

PHP-DI can also work with autowiring or annotations. These rely on the fact that the service name
is the class name (or interface name), e.g. you reference the entity manager by its class name
instead of `doctrine.orm.entity_manager`.

If you want to enjoy autowiring or annotations, you can simplify your life and write simple aliases
like these:

```php
return [
    'Psr\Log\LoggerInterface' => DI\get('logger'),
    // PHP 5.5 notation:
    ObjectManager::class => DI\get('doctrine.orm.entity_manager'),
];
```

Keep in mind that it's always better to type-hint against interfaces instead of class names!
So write your aliases with interfaces as much as possible.


## FOSRestBundle

There was a bug in FOSRestBundle that would prevent using "Controller as services" in some cases.

This bug has been fixed and will be released in >=1.3.2 (not released yet at the time of writing).

Full details are here: [FOSRestBundle#743](https://github.com/FriendsOfSymfony/FOSRestBundle/pull/743)


## More

Read more on the [PHP-DI-Symfony2 project on Github](https://github.com/mnapoli/PHP-DI-Symfony2).
