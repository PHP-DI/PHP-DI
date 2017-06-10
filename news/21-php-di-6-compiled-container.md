---
layout: blogpost
title: What's new in PHP-DI 6: Huge performance improvements with a compiled container
author: Matthieu Napoli
date: June 10th 2017
---

PHP-DI 5 was released two years ago and it introduced a powerful module system along with usability improvements.

It is now time to prepare for PHP-DI 6, the next major version which builds upon the same architecture but fixes two main issues:

- inconsistencies and confusion
- performances

This blog post introduces the performance improvements made thanks to compiling the container to optimized PHP code and a lot of micro-optimizations made over the course of several months.

## Goodbye caching

While initially rewritten to move from Doctrine Cache to PSR-16 (the simple cache standard), caching has eventually been removed entirely from PHP-DI. The cache was used to avoid re-generating definitions from PHP configuration arrays, autowiring or annotation. This problem is now gone since the container can be compiled (read below).

While this may be disappointing to contributors who worked on bringing PSR-16 into PHP-DI, I hope the performance gains behind this decision will make you happy instead!

## What does "compiling the container" mean?

PHP-DI is now a container that **can** be compiled. Compiling the container is optional, you only need to do this if you have performance issues. If you don't, then you don't have to care about all that.

If you do care, here is what happens when the container is compiled. Let's take the following configuration file:

```php
use function DI\create;
use function DI\get;

return [
    ShoppingCartService::class => create()
        ->constructor(get(Database::class)),
    Database::class => create()
        ->constructor('mysql://user:password@localhost:3306/my_database'),
];
```

When the container is not compiled, PHP-DI will evaluate all these configurations at runtime and use the reflection to create instances of these classes.

When the container is compiled, PHP code written specifically for your project is generated and written to disk. This PHP code is written once (when you deploy your code in production usually), which avoids a lot of operations at runtime. Here is what the code of the compiled container looks like (this is not the actual generated code, it's just an illustration):

```php
class CompiledContainer123456 extends DI\CompiledContainer
{
    public function createShoppingCartEntry()
    {
        return new ShoppingCartService($this->get(Database::class));
    }
    public function createDatabaseEntry()
    {
        return new Database('mysql://user:password@localhost:3306/my_database');
    }
}
```

As you can see, this PHP code is pretty simple.

## How to compile the container

Using that new feature is pretty simple and it is detailed in the [Performances](../doc/performances.md) article. You just have to call the `compile()` method when configuring the `ContainerBuilder`:

```php
$containerBuilder = new \DI\ContainerBuilder();
if (/* is production */) {
    $containerBuilder->compile(__DIR__ . '/var/cache/CompiledContainer.php');
}
```

## A note about closures

In the PHP ecosystem there are usually two kinds of DI containers:

- compiled containers, like Symfony, Yaco
- containers that allow defining services using closures, like Pimple

PHP-DI 6 will be, as far as I know, the first PHP container to be part of both categories, allowing to benefit from the simplicity of closures as well as the performance boost of compilation.

TODO Link
Compiling closures in PHP-DI is made possible thanks to the awesome `roave/better-reflection` library, which allows to analyze and manipulate closures. While this may sound like a bad idea at first, copying closures into the compiled container turns out to work pretty well. This feature is covered by an extensive test suite and I expect to take advantage of the beta period of v6 to test out a lot of scenarios.

Here is what a compiled container using closures look like:

```php
return [
    ShoppingCartService::class => function ($container) {
        return new ShoppingCartService($container->get(Database::class));
    },
    // ...
];

// Compiled container:
class CompiledContainer123456 extends DI\CompiledContainer
{
    public function createShoppingCartEntry()
    {
        $factory = function ($container) {
            return new ShoppingCartService($container->get(Database::class));
        };
        $factory($container);
    }
    // ...
}
```

The real generated code is actually a bit more complex than shown above but you get the idea. The only limitations found with compiling closures are:

- you cannot use `$this` inside closures
- you cannot import variables inside the closure using the `use` keyword, like in `function () use ($foo) { ...`

Those use cases do not make sense when defining factories so it is not a problem to not support them.
