---
layout: documentation
current_menu: performances
---

# Performances

## A note about caching

PHP-DI 4 and 5 relied a lot on caching. With PHP-DI 6 the main vector for optimization is now to compile the container into highly optimized code (see below). Compiling the container is simpler and faster.

## Compiling the container

PHP-DI performs two tasks that can be expensive:

- reading definitions from your [configuration](php-definitions.md), from [autowiring](autowiring.md) or from [annotations](annotations.md)
- resolving those definitions to create your services

In order to avoid those two tasks, the container can be compiled into PHP code optimized especially for your configuration and your classes.

### Setup

Compiling the container is as easy as calling the `enableCompilation()` method on the container builder:

```php
$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->enableCompilation(__DIR__ . '/var/cache');

// […]

$container = $containerBuilder->build();
```

The `enableCompilation()` method takes the path of the directory in which to store the compiled container.

### Deployment in production

When a container is configured to be compiled, **it will be compiled once and never be regenerated again**. That allows for maximum performances in production.

When you deploy new versions of your code to production **you must delete the generated file** (or the directory that contains it) to ensure that the container is re-compiled.

If your production handles a lot of traffic you may also want to generate the compiled container *before* the new version of your code goes live. That phase is known as the "warmup" phase. To do this, simply create the container (call `$containerBuilder->build()`) during your deployment step and the compiled container will be created.

### Development environment

**Do not compile the container in a development environment**, else all the changes you make to the definitions (annotations, configuration files, etc.) will not be taken into account. Here is an example of what you can do:

```php
$containerBuilder = new \DI\ContainerBuilder();
if (/* is production */) {
    $containerBuilder->enableCompilation(__DIR__ . '/var/cache');
}
```

### Optimizing for compilation

As you can read in the "*How it works*" section, PHP-DI will take all the definitions it can find and compile them. That means that definitions like **autowired classes that are not listed in the configuration cannot be compiled** since PHP-DI doesn't know about them.

If you want to optimize performances to a maximum in exchange for more verbosity, you can let PHP-DI know about all the autowired classes by listing them in definition files:

```php
return [
    // ... (your definitions)

    UserController::class => autowire(),
    BlogController::class => autowire(),
    ProductController::class => autowire(),
    // ...
];
```

You do not need to configure them (autowiring will still take care of that) but at least now PHP-DI will know about those classes and will compile their definitions.

Currently PHP-DI does not traverse directories to find autowired or annotated classes automatically.

It also does not resolve [wildcard definitions](php-definitions.md#wildcards) during the compilation process. Those definitions will still work perfectly, they will simply not get a performance boost when using a compiled container.

On the other hand factory definitions (either defined with closures or with class factories) are supported in the compiled container. However please note that if you are using closures as factories:

- you should not use `$this` inside closures
- you should not import variables inside the closure using the `use` keyword, like in `function () use ($foo) { ...`

These limitations exist because the code of each closure is copied into the compiled container. It is safe to say that you should probably not do these things even if you do not compile the container.

### How it works

PHP-DI will read definitions from your [configuration](php-definitions.md). When the container is compiled, PHP code will be generated based on those definitions.

For example let's take the definition for creating an object:

```php
return [
    'Logger' => DI\create()
        ->constructor('/tmp/app.log')
        ->method('setLevel', 'warning'),
];
```

This definition will be compiled to PHP code similar to this:

```php
$object = new Logger('/tmp/app.log');
$object->setLevel('warning');
return $object;
```

All the compiled definitions will be dumped into a PHP class (the compiled container) which will be written to a file (for example `CompiledContainer.php`).

At runtime, the container builder will see that the file `CompiledContainer.php` exists and will load it (instead of loading the definition files). That PHP file may contain a lot of code but PHP's opcode cache will cache that class in memory (remember to use opcache in production). When a definition needs to be resolved, PHP-DI will simply execute the compiled code and return the created instance.

## Optimizing lazy injection

If you are using the [Lazy Injection](lazy-injection.md) feature you should read the section ["Optimizing performances" of the guide](lazy-injection.md#optimizing-performances).

## Caching

Compiling the container is the most efficient solution, but it has some limits. The following cases are not optimized:

- autowired (or annotated) classes that are not declared in the configuration
- wildcard definitions
- usage of `Container::make()` or `Container::injectOn()` (because those are not using the compiled code)

If you make heavy use of those features, and if it slows down your application you can enable the caching system. The cache will ensure annotations or the reflection is not read again on every request.

The cache relies on APCu directly because it is the only cache system that makes sense (very fast to write and read). Other caches are not good options, this is why PHP-DI does not use PSR-6 or PSR-16 for this cache.

To enable the cache:

```php
$containerBuilder = new \DI\ContainerBuilder();
if (/* is production */) {
    $containerBuilder->enableDefinitionCache();
}
```

You can also pass an optional namespace argument to `enableDefinitionCache('my-namespace')` which will add the provided namespace to all PHP-DI cache keys. This is helpful to prevent cache collisions when sharing a single APCu memory pool between multiple DI containers. Here is an example of a PHP-DI cache key for a class named `MyClass` with, and without, a namespace:

- With namespace:  `php-di.definitions.my-namespaceMyClass`
- No namespace:    `php-di.definitions.MyClass`

Heads up:

- do not use a cache in a development environment, else changes you make to the definitions (annotations, configuration files, etc.) may not be taken into account
- clear the APCu cache on each deployment in production (to avoid using a stale cache)
