---
template: blogpost
title: PHP-DI 3.2 released
author: Matthieu Napoli
date: July 23rd 2013
---

I am happy to announce that I have just released PHP-DI version 3.2.

The major new feature is the full support of **Lazy Injection**. But first, let's talk about the `ContainerBuilder`.

## ContainerBuilder

The `ContainerBuilder` is an object helping you to configure and create your container.

```php
<?php
$builder = new \DI\ContainerBuilder();
$builder->setDefinitionCache(new Doctrine\Common\Cache\ArrayCache());
$builder->setDefinitionsValidation(true);

$container = $builder->build();
```

The documentation now presents 3 different configuration templates:

- development configuration
- production configuration
- lightweight configuration

Read the documentation: [Configuring the container](../doc/container-configuration.md).

## Lazy injection

Until v3.1, PHP-DI offered limited support for injecting dependencies lazily: it was restricted to property injection. Up from v3.2, there are no restrictions anymore: you can now inject lazily in constructors, setters or properties.

To achieve lazy dependencies, PHP-DI injects **proxies**. These proxies behave and look just like the real object, except they delay loading this object until it is really used.

This can be really helpful to improve performances if you tend to inject unused dependencies.

**Credits**: lazy injection relies on creating proxy classes. PHP-DI uses [ProxyManager](https://github.com/Ocramius/ProxyManager) by Marco Pivetta, the library now used by Symfony, Zend Framework, …. A big thanks to him.

### Example

You can mark dependencies to be lazily injected, here is an example using annotations:

```php
<?php
use DI\Annotation\Inject;

class Example {
    /**
     * @Inject(lazy=true)
     * @var My\Class
     */
    protected $property;

    /**
     * @Inject({ "param1" = {"lazy"=true} })
     */
    public function method(My\Class $param1) {
    }
}
```

Here is an example using YAML:

```yaml
Example:
  properties:
    property:
      name: My\Class
      lazy: true
  methods:
    method:
      param1:
        name: My\Class
        lazy: true
```

Read the full documentation: [Lazy injection](../doc/lazy-injection.md).


## Change log

Read all the changes in the [change log](../change-log.md).
