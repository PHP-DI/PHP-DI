# PHP-DI 3.1 released

*Posted by [Matthieu Napoli](https://github.com/mnapoli) on June 23, 2013*

I am happy to announce that I have just released PHP-DI version 3.1.

The major new feature is the **Zend Framework 1 integration**. You can now use PHP-DI very easily with ZF1, and the integration will provide you dependency injection into your controllers.

Mixing different definition sources (reflection, annotations, files, …) is now more reliable with orders and priorities. Sources are all now correctly priorized, allowing you to override definitions as you would expect. Read more in the [**Definition overriding** documentation](doc/definition-overriding.md).

Finally, [a small fix](https://github.com/mnapoli/PHP-DI/issues/79) to allow to **define `null` entries**:

```php
// Set a null value for 'foo'
$container->set('foo', null);

// No change: without a value given, returns a class definition helper
$container->set('foo')
    ->bindTo('My\Foo\Class')
    ->withConstructor(array('SomeClass'));
```

## Zend Framework 1 integration

To set up your ZF1 project, you can install the dependencies with [Composer](http://getcomposer.org/):

```php
{
    "require": {
        "mnapoli/php-di": "3.1.*",
        "mnapoli/php-di-zf1": "*"
    }
}
```

In your bootstrap, you only need to replace the default Dispatcher with the one provided into `mnapoli/php-di-zf1`:

```php
    protected function _initDependencyInjection() {
        $container = new \DI\Container();

        $dispatcher = new \DI\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($container);

        Zend_Controller_Front::getInstance()->setDispatcher($dispatcher);
    }
```

Easy right? Now you can inject dependencies into your controllers.

Here is an example using annotations (you can't use constructor injection though since ZF1 controllers have a specific constructor):

```php
use DI\Annotation\Inject;

class GuestbookController extends Zend_Controller_Action
{
    /**
     * @Inject
     * @var Application_Service_GuestbookService
     */
    private $guestbookService;

    public function signAction()
    {
        $form = new Application_Form_Guestbook();
        $this->guestbookService->addEntry($form->getValues());
    }
}
```

## Integration with other frameworks (Symfony, ZF2, …)

Integration with other frameworks [are planned](https://github.com/mnapoli/PHP-DI/issues?state=open).

If you want to help, feel free to submit a pull request or let's talk on [Twitter](https://twitter.com/PHPDI).
