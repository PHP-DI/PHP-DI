# PHP-DI in Zend Framework 1

If you are using ZF1, PHP-DI provides easy and clean integration so that you don't have
to call the container (thus avoiding the Service Locator pattern).

First, install the bridge to ZF1:

```json
{
    "require": {
        "mnapoli/php-di": "The version you want here",
        "mnapoli/php-di-zf1": "*"
    }
}
```

To use PHP-DI in your ZF1 application, you need to change the Dispatcher used by the Front Controller in the Bootstrap.

```php
    protected function _initDependencyInjection() {
        $container = new \DI\Container();

        $dispatcher = new \DI\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($container);

        Zend_Controller_Front::getInstance()->setDispatcher($dispatcher);
    }
```

**That's it!**

Now you have dependency injection **in your controllers**.

**Warning**: if you use Zend's autoloader (and not Composer), you will need to configure it:

```php
$autoloader->suppressNotFoundWarnings(true);
```

Read more on the [PHP-DI-ZF1 project on Github](https://github.com/mnapoli/PHP-DI-ZF1).
