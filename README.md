PHP-DI is a Container that makes [*Dependency Injection*](http://en.wikipedia.org/wiki/Dependency_injection)
as practical as possible.

PHP-DI also tries to avoid falling into the trap of the "Service Locator" antipattern and help you do *real* dependency injection.

[![Latest Stable Version](https://poser.pugx.org/mnapoli/php-di/v/stable.png)](https://packagist.org/packages/mnapoli/php-di) [![Total Downloads](https://poser.pugx.org/mnapoli/php-di/downloads.png)](https://packagist.org/packages/mnapoli/php-di)


## Features

* Simple to start with
* Supports different configuration alternatives to suit every taste:
    * **Reflection**: zero configuration, intelligent guessing
    * **Annotations**: modern, practical and simple
    * **PHP code**: if you like complete control and auto-completion
    * **PHP array**: allows you to store it in a configuration file
    * **YAML**: elegant and concise
    * **XML** (work in progress): more verbose, but more classic
* **Performances**: supports a large number of Caches
* Offers lazy injection: lazy-loading of dependencies (WIP)
* Supports constructor injection, setter injection and property injection


## Usage

Let's go to the [Getting started guide](doc/getting-started.md)!

And there is a [complete documentation](doc/) waiting for you.


## What is dependency injection, and why use PHP-DI

You can first read the [introduction to dependency injection with an example](doc/example.md).

Dependency injection and DI containers are separate notions, and one should use of a container only if it makes things more practical (which is not always the case depending on the container you use).

PHP-DI is about this: make dependency injection more practical.

### How classic PHP code works

Here is how a code **not** using DI will roughly work:

* Application needs Foo (e.g. a controller), so:
* Application creates Foo
* Application calls Foo
    * Foo needs Bar (e.g. a service), so:
    * Foo creates Bar
    * Foo calls Bar
        * Bar needs Bim (a service, a repository, …), so:
        * Bar creates Bim
        * Bar does something

### How Dependency Injection works

Here is how a code using DI will roughly work:

* Application needs Foo, which needs Bar, which needs Bim, so:
* Application creates Bim
* Application creates Bar and gives it Bim
* Application creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

This is the pattern of **Inversion of Control**. The control of the dependencies is **inversed** from one being called to the one calling.

The main advantage: the one at the end of the caller chain is always **you**. So you can control every dependencies: you have a complete control on how your application works. You can replace a dependency by another (one you made for example).

For example that wouldn't be so easy if Library X uses Logger Y and you have to change the code of Library X to make it use your logger Z.

### How code using PHP-DI works

Now how does a code using PHP-DI works:

* Application needs Foo so:
* Application gets Foo from the Container, so:
    * Container creates Bim
    * Container creates Bar and gives it Bim
    * Container creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

In short, PHP-DI takes away all the work of creating and injecting dependencies.
