---
template: documentation
tab: get-started
---

# Getting started with PHP-DI

Hi there and welcome! This guide will help you get started with using PHP-DI in your project.

Before beginning, you need to know what dependency injection is. If you don't, there's a whole article dedicated to it: [read it](understanding-di.md) and come back once finished.

## Installation

Install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md):

```json
composer require mnapoli/php-di
```

## Basic usage

### 1. Use dependency injection

Let's use dependency injection without thinking about PHP-DI:

```php
class Mailer
{
    public function mail($recipient, $content)
    {
        // send an email to the recipient
    }
}

class UserManager
{
    private $mailer;

    public function __construct(Mailer $mailer) {
        $this->mailer = $mailer;
    }

    public function register($email, $password)
    {
        // The user just registered, we create his account
        // ...

        // We send him an email to say hello!
        $this->mailer->mail($email, 'Hello and welcome!');
    }
}
```

As we can see, the `UserManager` takes the `Mailer` as a constructor parameter: this is dependency injection!

### 2. Create the objects

Without PHP-DI, we would have to "wire" the dependencies manually like this:

```php
$mailer = new Mailer();
$userManager = new UserManager($mailer);
```

Instead, we can let PHP-DI figure out the dependencies:

```php
$userManager = $container->get('UserManager');
```

Behind the scenes, PHP-DI will create both a Mailer object and a UserManager object.

**How does it know what to inject?**

The container uses a technique called **autowiring**. This is not unique to PHP-DI, but this is still awesome. It will scan the code and see what are the parameters needed in the constructors.

In our example, the `UserManager` constructor takes a `Mailer` object. So PHP-DI knows that it needs to create one. Pretty basic, but very efficient.

*Wait, isn't that weird and risky to scan PHP code like that?* Don't worry, PHP-DI uses PHP's Reflection, this is pretty standard stuff. Laravel, Zend Framework or any decent container does the same.

## Defining injections

So we have covered **autowiring**, which is when PHP-DI figures out automatically the dependencies a class needs.

But in total you have 3 ways to define what to inject in a class:

- autowiring (i.e. nothing to do)
- write configuration files
- use annotations










# Getting started

## Installation

PHP-DI works with **PHP 5.3** or higher. But seriously, use 5.5 or even 5.6.

The easiest way is to install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md).
Create a file named `composer.json` in your project root:

```json
{
    "require": {
        "mnapoli/php-di": "~4.0"
    }
}
```

Then, run the following commands:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

If you don't use Composer, you can directly [download](https://github.com/mnapoli/PHP-DI/releases) the sources and configure it with your autoloader.


## Define dependencies


You have to define a dependency graph between your objects, which we can represent like so (nodes are objects, links are dependencies):

![](graph.png)

PHP-DI offers several ways to define dependencies, so use which ones you like.

Below is a quick introduction to some options, but you can also read [the full documentation](definition.md).


### Autowiring

PHP-DI can use [PHP Reflection](http://fr.php.net/manual/fr/book.reflection.php) to understand what parameters a constructor needs:

```php
class Foo {
    private $bar;

    public function __construct(Bar $bar) {
        return $this->bar = $bar;
    }
}
```

PHP-DI will know that it should inject an instance of the `Bar` interface or class.

**No configuration needed!**

Of course, this comes with limitations:

- only works with constructor injection
- only works if your parameters are classes, and are typed (else PHP-DI can't guess what you expect)
- if the type of the parameter is an interface, you will need to configure which implementation to use in the config file (see below)

However, autowiring generally covers 80% of the cases.

### Annotations

You can also use annotations to define injections, here is a short example:

```php
<?php

class Foo {
    /**
     * @Inject
     * @var Bar
     */
    protected $bar;

    /**
     * @Inject
     */
    public function setBaz(Baz $bin) {
    }

    /**
     * @Inject({"db.host", "db.port"})
     */
    public function setValues($param1, $param2) {
    }
}
```

See also the [complete documentation about annotations](definition.md).

### PHP array

You can define injections with a PHP array too (this example uses PHP 5.4 and 5.5 features):

```php
<?php
return [

    // Values
    'db.host' => 'localhost',
    'db.port' => 5000,

    // Class
    MyDbAdapter::class => DI\object()
        ->constructor(DI\link('db.host'), DI\link('db.port')),

];
```

See also the [complete documentation about array configuration](definition.md).

You need to configure the container to import this file:

```php
$builder = new ContainerBuilder();
$builder->addDefinitions('config.php');

$container = $builder->build();
```


## Get objects from the container

```php
$foo = $container->get('Foo');
```

But wait! Do not use this everywhere because this makes your code **dependent on the container**.
This is an antipattern to dependency injection (it is like the service locator pattern: dependency *fetching* rather than *injection*).

So PHP-DI container should be called at the root of your application (in your Front Controller for example).
To quote the Symfony docs about Dependency Injection:

> You will need to get [an object] from the container at some point but this should be as few times as possible at the entry point to your application.

For this reason, we are trying to provide integration with MVC frameworks (see below).

To sum up:

- If you can, use `$container->get()` in you root application class or front controller
- Else, use `$container->get()` in your controllers (but avoid it in your services) but keep in mind that your controllers will be dependent on the container

#### Frameworks integration

- [Symfony 2](frameworks/symfony2.md)
- [Zend Framework 1](frameworks/zf1.md)
- [Zend Framework 2](https://github.com/mnapoli/PHP-DI-ZF2) (beta version)


## What's next

You can head over to [the documentation index](README.md).

You can also read the [Best practices guide](best-practices.md), it's a good way to get a good view on
when to use each of PHP-DI's features.

Here are some other topics that might interest you right now:

- [Configuring the container](container-configuration.md)
- [Define injections](definition.md)
