---
layout: documentation
current_menu: zf1
---

# PHP-DI in Zend Framework 1

## Set up

If you are using ZF1, PHP-DI provides easy and clean integration so that you don't have
to call the container (thus avoiding the Service Locator pattern).

First, install the bridge to ZF1:

```
composer require php-di/zf1-bridge
```

To use PHP-DI in your ZF1 application, you need to change the Dispatcher used by the Front Controller in the Bootstrap.

```php
    protected function _initContainer()
    {
        $builder = new \DI\ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();

        $dispatcher = new \DI\Bridge\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($container);

        Zend_Controller_Front::getInstance()->setDispatcher($dispatcher);
    }
```

That's it!

As you can see since PHP-DI 5 it's necessary to enable [annotations](../annotations.md) because they are disabled by default.

**Warning**: if you use Zend's autoloader (and not Composer), you will need to configure it:

```php
$autoloader->suppressNotFoundWarnings(true);
```

## Usage

Now you can inject dependencies in your controllers!

For example, here is the GuestbookController of the quickstart:

```php
class GuestbookController extends Zend_Controller_Action
{
    /**
     * This dependency will be injected by PHP-DI
     * @Inject
     * @var Application_Service_GuestbookService
     */
    private $guestbookService;

    public function indexAction()
    {
        $this->view->entries = $this->guestbookService->getAllEntries();
    }
}
```

## Recommended layout

Here is a recommended layout for your configuration:

```
application/
    configs/
        application.ini          # ZF config
        config.php               # DI config
        config.development.php   # DI config for development
        config.production.php    # DI config for production
        parameters.php           # Local parameters (DB password, …) -> Don't commit this file
        parameters.php.default   # Template for parameters.php -> Commit this file
    Bootstrap.php
```

Here is an example of the full Bootstrap method:

```php
    protected function _initContainer()
    {
        $configuration = new Zend_Config($this->getOptions());

        $builder = new ContainerBuilder();
        $builder->addDefinitions(APPLICATION_PATH . '/configs/config.php');
        $builder->addDefinitions(APPLICATION_PATH . '/configs/config.' . APPLICATION_ENV . '.php');
        $builder->addDefinitions(APPLICATION_PATH . '/configs/parameters.php');

        if (APPLICATION_ENV === 'production') {
            $cache = new MemcachedCache();
            $memcached = new Memcached();
            $memcached->addServer('localhost', 11211);
            $cache->setMemcached($memcached);
        } else {
            $cache = new ArrayCache();
        }
        $cache->setNamespace('MyApp');
        $builder->setDefinitionCache($cache);

        $this->container = $builder->build();

        $dispatcher = new \DI\Bridge\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($this->container);
        Zend_Controller_Front::getInstance()->setDispatcher($dispatcher);
    }
```

You are not required to follow it of course.

## Tests

Zend Framework 1 provides [a base class to test controllers](http://framework.zend.com/manual/1.12/en/zend.test.phpunit.html).
If you are using this base class, and you want to have full control on the dependencies that will be injected
inside the controller you are testing (for example to inject **mocks**), here is a recommended approach:

```php
class UserControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private $container;

    public function setUp()
    {
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function appBootstrap()
    {
        $builder = new ContainerBuilder();
        // Configure your container here
        $builder->addDefinitions(__DIR__ . '/../../application/config/config.php');
        $this->container = $builder->build();

        $dispatcher = new \DI\Bridge\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($this->container);

        $this->frontController->setDispatcher($dispatcher);
    }

    public function testCallWithoutActionShouldPullFromIndexAction()
    {
        // Here I can override the dependencies that will be injected
        $fakeEntityManager = $this->getMock(...);
        $this->container->set('Doctrine\ORM\EntityManager', $fakeEntityManager);

        $this->dispatch('/user');
        $this->assertController('user');
        $this->assertAction('index');
    }
}
```

How it works: a new container is created for each test, which ensures that whatever you do
with the container in one test will not affect the others.

Obviously, the `setUp()` and `appBootstrap()` methods could go in a base abstract class.

## More

Read more on the [ZF1-Bridge project on GitHub](https://github.com/PHP-DI/ZF1-Bridge).
