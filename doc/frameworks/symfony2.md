---
template: documentation
---

# PHP-DI in Symfony 2

If you are using Symfony 2, PHP-DI provides easy and clean integration, without replacing the original container
(so all bundles and existing code still work).

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

**That's it!**


You can now define [controllers as services](http://symfony.com/doc/current/cookbook/controller/service.html),
without any configuration, using PHP-DI's magic!

Example for the routing configuration:

```yaml
my_route:
    pattern:  /product-stock/clear
    defaults: { _controller: MyBundle\Controller\ProductController:clearAction }
```

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

Read more on the [PHP-DI-Symfony2 project on Github](https://github.com/mnapoli/PHP-DI-Symfony2).
