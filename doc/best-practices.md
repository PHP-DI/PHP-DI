---
layout: documentation
tab: best-practices
current_menu: best-practices
---

# Best practices

This is an opinionated guide on how to use PHP-DI and dependency injection for the best.

While it may not cover every case, and satisfy everybody, it can serve as a canvas to help you
getting started with dependency injection.

If you disagree with anything explained in that guide, that's OK. It's opinionated, and you
should make your own opinion on all these subjects ;). It shouldn't prevent you to use PHP-DI
the way you want.

## Rules for using a container and dependency injection

Here are some basic rules to follow:

1. never get an entry from the container directly (always use dependency injection)
2. more generally, write code decoupled from the container
3. type-hint against interfaces, configure which implementation to use in the container's configuration


## Writing controllers

Using dependency injection in controllers is usually where it is the most painful.

If we take Symfony 2 as an example (but this generally applies to every framework), here are your options:

- inject the container in the controllers, and call `$container->get(...)`

This is bad, see the rule n°1.

- inject dependencies in the constructor ([controller as a service in Symfony](http://symfony.com/doc/current/cookbook/controller/service.html))

This is painful when you have more than 5 dependencies, and your constructor is
[15 lines of boilerplate code](http://www.whitewashing.de/2013/06/27/extending_symfony2__controller_utilities.html)

- **inject dependencies in properties**

This is the solution we recommend.

Example:

```php
class UserController
{
    /**
     * @Inject
     */
    private FormFactoryInterface $formFactory;

    public function createForm($type, $data, $options)
    {
        // $this->formFactory->...
    }
}
```

As you can see, this solution requires very little code, is simple to understand and benefits from IDE support
(autocompletion, refactoring, …).

Property injection is generally frowned upon, and for good reasons:

- injecting in a private property breaks encapsulation
- it is not an explicit dependency: there is no contract saying your class need the property to be set to work
- if you use PHP-DI's annotations to mark the dependency to be injected, your class is dependent on the container (see the 2nd rule above)

BUT

if you follow general best practices on how to write your application, your controllers
will not contain business logic (only routing calls to the models and binding returned values to view).

So:

- you will not unit-test it (that doesn’t mean you won’t write functional tests on the interface though)
- you will not need to reuse it elsewhere
- if you change the framework, you may have to rewrite it (or parts of it) anyway
(because most dependencies like Request, Response, templating system, etc. will have changed)

This solution offers many benefits for no major drawback, so
**we recommend using annotations in controllers**.


## Writing services

Given a service is intended to be reused, tested and independent of your framework, **we do not recommend
using annotations for injecting dependencies**.

Instead, we recommend using **constructor injection and autowiring**:

```php
class OrderService implements OrderServiceInterface
{
    private $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processOrder($order)
    {
        $this->paymentService->...
    }
}
```

By using autowiring (enabled by default), you save yourself binding every parameter
of the constructor in the configuration. PHP-DI will guess which object it needs to inject by checking
the types of your parameters.

In some cases, autowiring will not be enough because some parameter will be a scalar (string, int, …).
At that point, you will need to define explicitly what to inject in that scalar parameter, and for this you can either:

- define the whole injection of the method/class (i.e. every parameters) using `DI\create()`.

Example:

```php
<?php
// config.php
return [
    // ...
    OrderService::class => DI\create()
        ->constructor(DI\get(SomeOtherService::class), 'a value'),
];
```

- or define *just the scalar parameter* using `DI\autowire()` and let PHP-DI autowire the rest.

Example:

```php
<?php
// config.php
return [
    // ...
    OrderService::class => DI\autowire()
        ->constructorParameter('paramName', 'a value'),
];
```

This solution is generally preferred given it avoids redefining everything.

*Side note:* as explained in rule n°3, we recommend **type-hinting against interfaces**. In that case,
you will need to map interfaces to the implementation the container should use in the configuration:

```php
<?php
// config.php
return [
    // ...
    OrderServiceInterface::class => DI\get(OrderService::class),
];
```


## Using libraries

When using libraries, like loggers, ORMs, … you sometimes need to configure them.

In that case, we advise you to define these dependencies in your configuration file.
We also recommend using an anonymous function when the configuration gets a bit complex.

**The anonymous function allows you to write real PHP code**, which is great, because you
can use the library's documentation, you get IDE support, and you are a PHP developer so
you already know the language ;).

Here is an example with [Monolog](https://github.com/Seldaek/monolog), a PHP logger:

```php
<?php
// config.php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return [
    // ...

    Psr\Log\LoggerInterface::class => DI\factory(function () {
        $logger = new Logger('mylog');

        $fileHandler = new StreamHandler('path/to/your.log', Logger::DEBUG);
        $fileHandler->setFormatter(new LineFormatter());
        $logger->pushHandler($fileHandler);

        return $logger;
    }),
];
```

Of course, as you can see, we used the PSR-3 interface for injections. That way we can replace Monolog
with any PSR-3 logger anytime we want, just by changing this configuration.
