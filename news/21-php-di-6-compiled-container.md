---
layout: blogpost
title: "PHP-DI 6: turning into a compiled container for maximum performances"
author: Matthieu Napoli
date: January 24th, 2018
---

PHP-DI's motto is "The dependency injection container for humans".

This is very important because it defines what PHP-DI tries to be: practical to use, consistent, predictable, with a configuration format that is easy to read and write and a great documentation.

It also defines what PHP-DI does not try to be, and that is as much important. PHP-DI does not try to be the smallest, or the fastest container out there. And PHP-DI 5 is by far not one of the fastest containers.

But the good thing is that, after 6 years of existence, the project has matured and is now quite stable. The original objectives are met, even though there is of course always room for improvements and innovation. There is room to push the container to be better on other levels. And the most obvious one is **performances**.

PHP-DI 6 will be much, much faster because it is a compiled container.

## What does "compiling" mean?

Imagine the following class:

```php
class Foo
{
    public function __construct(Bar $bar) { … }
}
```

And the following configuration file:

```php
return [
  
    Foo::class => create()
        ->constructor(get(Bar::class)),
  
    // Let's use a closure just for the sake of the example
    Bar::class => function() {
        return new Bar('Hello world');
    },
  
];
```

If we were to implement this without any containers, here is how we would instantiate `Foo` and `Bar`:

```php
$bar = new Bar('Hello world');
$foo = new Foo($bar);
```

In a container, things go differently because the container is generic: it can handle 0 or many parameters, with references to a lot of entries, etc. To do that, the container constructs *definition objects*. A definition explains *how* to create an instance. More concretely, it is a class that stores the number of parameters of the constructor, which parameters need to be injected, etc.

If you are curious, you can read [PHP-DI's `ObjectCreator` class](https://github.com/PHP-DI/PHP-DI/blob/b6ff39dd7eb3a67485af7992274367acc2d04b6e/src/Definition/Resolver/ObjectCreator.php#L114-L162): it takes a definition object and creates an object.

This process involves using PHP's Reflection API and a lot of logic (especially if the container is quite smart). And of course, this takes time. This is actually where PHP-DI spends most of its time. This is also the reason why "simple" containers like Pimple are faster than more complex ones: they have very simple (and limited) logic for creating objects.

**Compiling the container means bypassing all that**. The idea is to take all the definitions and turn them into optimized PHP code, close to the code we would write ourselves (except we don't have to write it ourselves).

Here is an theoretical example of a compiled container (not actual PHP-DI generated code):

```php
// The class has a random number in there because it's auto-generated
class CompiledContainer123456 extends BaseCompiledContainer
{
    public function get($id)
    {
        // Method implemented in the parent class.
        // Basically it calls `$this->create{$id}()`
    }
  
    protected function createFoo()
    {
        return new Foo($this->get('bar'));
    }
  
    protected function createBar()
    {
        return new Bar('Hello world');
    }
}
```

Compiling the container means generating that class. Instead of manipulating definitions, the compiled container simply calls code that is optimized for your objects and your project.

### Compiling closures

PHP-DI is not the first compiled container. [Symfony's approach](https://symfony.com/doc/current/components/dependency_injection.html) was a huge inspiration on how to tackle this problem.

However one thing specific to PHP-DI 6 is that even closures are compiled. For example:

```php
return [
    Foo::class => function() {
        return new Foo();
    },
];
```

The `Foo` entry will be compiled and will benefit from the performance improvements. This will work even if the closure takes parameters, uses type-hinting, etc. This is made possible thanks to the awesome [SuperClosure](https://github.com/jeremeamia/super_closure) and [PHP-Parser](https://github.com/nikic/PHP-Parser) projects. The only other container that I know of that can compile closures is [Yaco](https://github.com/thecodingmachine/yaco).

### Usage

Compiling the container is very simple, you simply have to call `enableCompilation()`:

```php
$containerBuilder = new \DI\ContainerBuilder();

$containerBuilder->addDefinitions([
    Foo::class => create()
]);

$containerBuilder->enableCompilation(__DIR__ . '/var/cache');
$container = $containerBuilder->build();
```

The first time this code is run, the container will be compiled into a PHP file in the `var/cache` directory. On all future executions the compiled container will be used directly.

Integrating that with your deployment script should be easy too: simply clear the directory on every deploy and PHP-DI will recompile the container. You can also pre-generate the container in advance in your deploy script, just run the code above once.

## Performances

### Externals.io

Here is a comparison running PHP-DI 6 on [externals.io](https://externals.io) (measuring the load time of the home page):

- with cache enabled: 37ms
- with cache **and compilation** enabled: 28ms

That means a **23% improvement in the total loading time**, and 6% less memory used.

[![](https://i.imgur.com/O1Wddn6.png)](https://blackfire.io/profiles/compare/68d775b5-39bb-4cd8-87b3-51b75a297377/graph)

([Blackfire comparison](https://blackfire.io/profiles/compare/68d775b5-39bb-4cd8-87b3-51b75a297377/graph))

Of course in a more complex application the time spent in the container will be lower so the gains will probably be lower.

On a side note, we can also compare v6 with v5 ([Blackfire profile](https://blackfire.io/profiles/compare/3b8775f6-35e9-458f-882b-31ede384ab28/graph)):

- PHP-DI 5 with cache: 42ms
- PHP-DI 6 with cache: 37ms

Even without compilation, [externals.io](https://externals.io) runs 12% faster on v6 than on v5. This is thanks to many optimizations added during the development of version 6.

In total, with compilation enabled, **migrating from PHP-DI 5 to PHP-DI 6 gives [a 32% performance improvement](https://blackfire.io/profiles/compare/647ec5b4-08f4-4a82-821f-f1ef7be9cad8/graph) on externals.io**.

[![](https://i.imgur.com/AdIVPuL.png)](https://blackfire.io/profiles/compare/647ec5b4-08f4-4a82-821f-f1ef7be9cad8/graph)

### DI container benchmark

The gains shown above can also be seen in the [kocsismate di-container-benchmarks](https://github.com/kocsismate/php-di-container-benchmarks).
Here is a comparison of the results for PHP-DI 5 and PHP-DI 6. The left column is v5, the right is v6, and higher in the list is better:

- Test suite 1

<div class="row">
    <div class="col-sm-6"><img src="https://i.imgur.com/xPSb2Sd.png"></div>
    <div class="col-sm-6"><img src="https://i.imgur.com/HAm4cQS.png"></div>
</div>

- Test suite 2

<div class="row">
    <div class="col-sm-6"><img src="https://i.imgur.com/W20UJMT.png"></div>
    <div class="col-sm-6"><img src="https://i.imgur.com/Ekj5WgC.png"></div>
</div>

- Test suite 5

<div class="row">
    <div class="col-sm-6"><img src="https://i.imgur.com/5l6Xn87.png"></div>
    <div class="col-sm-6"><img src="https://i.imgur.com/b1vls8Y.png"></div>
</div>

- Test suite 6

<div class="row">
    <div class="col-sm-6"><img src="https://i.imgur.com/gxN7lJT.png"></div>
    <div class="col-sm-6"><img src="https://i.imgur.com/evEQGHh.png"></div>
</div>

PHP-DI now ranks in the fastest containers. Needless to say I am very happy with the results, especially since it is one of the most feature-rich containers.

## Conclusion

Compiling the container brings massive performance improvements for the container and finally makes PHP-DI one of the fastest DI containers out there.

Let's keep in mind however that DI containers usually represents a small part of most application's run time. While I'm very happy to report all these improvements, do not expect your application to run twice faster in production ;)

Want to try this out? PHP-DI 6.0 will be released in the next weeks, in the meantime give the latest beta a try: https://github.com/PHP-DI/PHP-DI/releases
