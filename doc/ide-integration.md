---
layout: documentation
---

# IDE integration

Using IDE can strongly increase your productivity.

Unfortunately main IDE features (like code completion, search for usages, renaming and moving stuff around) don't
work with PHP-DI container out of the box. Hovewer there are some tips that will help you to solve this problem.

## Does it really needed?

Most part of your code should not even know that dependency injection container is being used.
You should avoid injecting container itself into your services.
It is highly recommended **not to do like this**:
```php
<?php

class MyService
{
    //!!! THIS IS A BAD PRACTICE
    //!!! never inject container into services directly
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function doFoo()
    {
        $service1 = $this->container->get('service1');
        $service2 = $this->container->get('service2');
        $service1->foo();
        $service2->foo();
    }

}

```

In this code DI container is used as [service-locator](https://en.wikipedia.org/wiki/Service_locator_pattern).
This makes `MyService` class depending on the whole application, it becomes harder to maintain and refactor it, writing
unit-tests (or specs) becomes harder too.

But in some places of code using container to retreive services could be reasonable. This places are:
- controllers - probably, it would be better to inject services into controllers, but if your controllers are
well-designed and contain no business logic it will be okay to use container in controllers directly.
If controllers are thin it will be easy enough to maintain them, and usually you don't write unit-tests for controllers.
- CLI commands - same as controllers.
- functional testing - using container directly in functional testing makes some things easier, contexts (when speaking
about Behat) become more clear and readable and usually brings no problems.

So usually you don't even need code completion for DI container, but in some places it might be useful. And there are
several methods to achieve IDE support.


## Basic method

Basic approach to integration with IDE is using `@var` comment blocks to specify type of service. Using this method your
code could be the following:

```php

class UserRepository
{
    public function findByName($name)
    {
        //...
    }
}

class UserController
{

    public function createAction()
    {
        /** @var $repository UserRepository **/
        $repository = $this->container->get(UserRepository::class);

        //now IDE knows that $repository variable is instance of UserRepository
    }

}
```

Here you will get autocompletion for most popular IDEs like PHPStorm and NetBeans. But writing docblocks for every
service can be annoying, and there are better ways.


## Integration with JetBrains PHPStorm IDE

There are several methods that will allow you to use full power of PHPStorm IDE while working with PHP-DI or [any
other container](https://github.com/container-interop/container-interop#compatible-projects) that implement
`\Interop\Container\ContainerInterface`.

### Advanced metadata approach

Just put a file named `.phpstorm.meta.php` to the root of your project with content
```php
<?php
namespace PHPSTORM_META
{
    $STATIC_METHOD_TYPES = [
        \DI\Container::get('') => [
            "" == "@",
        ],
        \Interop\Container\ContainerInterface::get('') => [
            "" == "@",
        ],
    ];
}
```

Don't forget to restart IDE after creating file.

The only requirement to your PHP-DI configuration and client code is defining and fetching your services using
php5.5 [::class syntax](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class).
For instance, when configuration file contains the following:
```php
<?php

use Psr\Log\LoggerInterface;

return [

    LoggerInterface::class => function(){
        $logger = new \Monolog\Logger('app');

        $stdoutStream = new \Monolog\Handler\StreamHandler('php://stdout');
        $logger->pushHandler($stdoutStream);

        return $logger;
    },
];

```

you will be able to use code completion for

```php
//somewhere in CLI command
$this->getContainer()->get(LoggerInterface::class)->debug('hello world');
```

This feature is described in [PHPStorm documentation](https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata).


### Using plugin

There is plugin for PHPStorm that brings code completion and refactorings for PHP-DI container:
[pulyaevskiy/phpstorm-phpdi](https://github.com/pulyaevskiy/phpstorm-phpdi). It can be found in IDE Plugins menu list
(you can search for *PHP-DI*).

It supports PHPStorm 8 and 9.

To use this plugin you should define and fetch services using `::class` syntax (as described above).

This plugin very simple. It resolves all calls of method `get()` (for all objects) containing `::class` substring.
Usually it is not a problem, and sometimes it can even be useful. With this plugin you can do the following in your
symfony2 controllers:

```php
    public function createTaskAction()
    {
        $this->get(TaskCreator::class)->createFooTask();

        //...
    }
```

and code completion for `$this->get(TaskCreator::class)` will work as expected.

If you work with symfony2 application using PHP-DI as main DI container, symfony2-plugin and
[pulyaevskiy/phpstorm-phpdi](https://github.com/pulyaevskiy/phpstorm-phpdi) allow you to use full power of your IDE.
