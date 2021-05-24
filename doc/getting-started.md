---
layout: documentation
title: Getting started
current_menu: getting-started
---

# Getting started with PHP-DI

Welcome! This guide will help you get started with using PHP-DI in your project.

Before beginning, you need to know what dependency injection is. If you don't, there's a whole article dedicated to it: [Understanding dependency injection](understanding-di.md).

## Installation

Install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md):

```
composer require php-di/php-di
```

PHP-DI requires PHP 7.2 or above.

## Basic usage

### 1. Use dependency injection

First, let's write code using dependency injection without thinking about PHP-DI:

```php
class Mailer
{
    public function mail($recipient, $content)
    {
        // send an email to the recipient
    }
}
```

```php
class UserManager
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
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

### 2. Create the container

You can create a container instance pre-configured for development very easily:

```php
$container = new DI\Container();
```

If you want to register definition files (explained in [PHP definitions](php-definitions.md)) or tweak some options, you can use the [container builder](container-configuration.md):

```php
$builder = new DI\ContainerBuilder();
$builder->...
$container = $builder->build();
```

### 3. Create the objects

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

> How does it know what to inject?

The container uses a technique called **autowiring**. This is not unique to PHP-DI, but this is still awesome. It will scan the code and see what are the parameters needed in the constructors.

In our example, the `UserManager` constructor takes a `Mailer` object: PHP-DI knows that it needs to create one. Pretty basic, but very efficient.

> Wait, isn't that weird and risky to scan PHP code like that?

Don't worry, PHP-DI uses [PHP's Reflection classes](http://php.net/manual/en/book.reflection.php) which is pretty standard: Laravel, Zend Framework and many other containers do the same. Performance wise, such information is read once and then cached, it has no impact.

## Defining injections

We have seen **autowiring**, which is when PHP-DI figures out automatically the dependencies a class needs. But we have 3 ways to define what to inject in a class:

- using [autowiring](autowiring.md)
- using [annotations](annotations.md)
- using [PHP definitions](php-definitions.md)

Every one of them is different and optional. Here is an example of PHP definitions in a file:

```php
return [
    'api.url'    => 'http://api.example.com',
    'Webservice' => function (Container $c) {
        return new Webservice($c->get('api.url'));
    },
    'Controller' => DI\create()
        ->constructor(DI\get('Webservice')),
];
```

Please read the [Defining injections](definition.md) documentation to learn about autowiring, annotations and PHP definitions.

## Framework integration

We have seen in the example above that we can use the container to get objects:

```php
$userManager = $container->get('UserManager');
```

However we don't want to call the container everywhere in our application: it would **couple our code to the container**. This is known as the *service locator antipattern* - or dependency *fetching* rather than *injection*.

To quote the Symfony documentation:

> You will need to get [an object] from the container at some point but this should be as few times as possible at the entry point to your application.

For this reason, PHP-DI integrates with some frameworks so that you don't have to call the container (dependencies are injected in controllers):

- [Symfony](frameworks/symfony2.md)
- [Slim](frameworks/slim.md)
- [Silex](frameworks/silex.md)
- [Zend Framework 2](frameworks/zf2.md)
- [Zend Expressive](frameworks/zend-expressive.md)
- [Silly](frameworks/silly.md)

If you want to use PHP-DI with another framework or your own code, try to use `$container->get()` in you root application class or front controller. Have a look at this [**demo application**](https://github.com/PHP-DI/demo) built around PHP-DI for a practical example.

## What's next

You can head over to [the documentation index](README.md). You can also read the [Best practices guide](best-practices.md), it's a good way to get a good view on when to use each of PHP-DI's features.

Here are some other topics that might interest you right now:

- [Configuring the container](container-configuration.md)
- [Defining injections](definition.md)
