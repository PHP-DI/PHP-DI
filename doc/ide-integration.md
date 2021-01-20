---
layout: documentation
current_menu: ide-integration
---

# IDE integration

Ideally, application code [should not use a DI container directly](best-practices.md): dependency injection should be preferred. However there are situations where calling the container directly might happen:

- when writing root application classes (front controllers, …) or more generally frameworks
- when writing factories
- when maintaining or migrating legacy application
- when writing functional tests
- ...

In those situations, being able to benefit from full IDE features like autocompletion, refactoring support, etc. is very valuable.

## Inline PhpDoc

A basic approach to IDE support is using `@var` tags in docblocks:

```php
/** @var $repository UserRepository */
$repository = $this->container->get(UserRepository::class);

// the IDE can now autocomplete this statement
$repository->
```

This solution is simple and works great when your container is used rarely.

## PhpStorm integration

### Metadata file

[PhpStorm will load metadata from a `.phpstorm.meta.php` file](https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata) if it exists at the root of your project. Here is an example that adds support for PHP-DI as well as [any PSR-11 container](http://www.php-fig.org/psr/psr-11/):

```php
<?php
namespace PHPSTORM_META
{
    override(\Psr\Container\ContainerInterface::get(0), map([
        '' => '@',
    ]));
    override(\DI\Container::get(0), map([
        '' => '@',
    ]));
}
```

That configuration will make PhpStorm assume that anything returned by `->get('...')` is an instance of the first argument. For example `->get('DateTime')` (or `->get(DateTime::class)`) will be recognized to return a `DateTime` object.

This however will not work if your service name is not a class or interface, for example `->get('foo.bar')` will not be understood by PhpStorm.

**Note:** you may need to restart your IDE after adding this file to make sure PhpStorm takes it into account.

### PhpStorm plugin

If you don't fancy writing a `.phpstorm.meta.php` file in each of your projects, you can install the [PHP-DI plugin for PhpStorm](https://github.com/pulyaevskiy/phpstorm-phpdi) created by [Anatoly Pulyaevskiy](https://github.com/pulyaevskiy). The plugin can be installed in PhpStorm by searching for *PHP-DI* in the 3rd party plugin list.

This plugin offers the same benefits as `.phpstorm.meta.php`, and has the same limitations.
