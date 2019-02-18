---
layout: documentation
current_menu: lazy-injection
---

# Lazy injection

This feature should not be confused with lazy initialization of objects: **PHP-DI always creates objects only when they are requested or injected somewhere.**

Lazy injection goes further than this: it allows to defer the creation of an object's dependencies to the moment when they are actually used, not before.

**Warning: this feature should only be used exceptionally, please read the "When to use" section at the end of this page.**

## Example

```php
<?php
class ProductExporter
{
    private $pdfWriter;
    private $csvWriter;

    public function __construct(PdfWriter $pdfWriter, CsvWriter $csvWriter)
    {
        $this->pdfWriter = $pdfWriter;
        $this->csvWriter = $csvWriter;
    }

    public function exportToPdf()
    {
        $this->pdfWriter->write(...);
    }

    public function exportToCsv()
    {
        $this->csvWriter->write(...);
    }
}

$productExporter = $container->get(ProductExporter::class);
$productExporter->exportToCsv();
```

In this example the `exportToPdf()` is not called. `PdfWriter` is initialized and injected in the class but it's never used.

**If** `PdfWriter` was costly to initialize (for example if it has a lot of dependencies or if it does expensive things in the constructor) lazy injection can help to avoid instantiating the object **until it is used**.

## How it works

If you define an object as "lazy", PHP-DI will inject:

- the object, if it has already been created
- or else a **proxy** to the object, if it is not yet created

The proxy is a special kind of object that **looks and behaves exactly like the original object**, so you can't tell the difference. The proxy will instantiate the original object only when needed.

Creating a proxy is complex. For this, PHP-DI relies on [ProxyManager](https://github.com/Ocramius/ProxyManager), the (amazing) library used by Doctrine, Symfony and Zend.

Let's illustrate that with an example. For the sake of simplicity we will not inject a lazy object but we will ask the container to return one:

```php
class Foo
{
    public function doSomething()
    {
    }
}

$container->set('Foo', \DI\create()->lazy());

// $proxy is a Proxy object, it is not initialized
// It is very lightweight in memory
$proxy = $container->get('Foo');

var_dump($proxy instanceof Foo); // true

// Calling a method on the proxy will initialize it
$proxy->doSomething();
// Now the proxy is initialized, the real instance of Foo has been created and called
```

## How to use

You can define an object as "lazy". If it is injected as a dependency, then a proxy will be injected instead.

### Installation

Lazy injection requires the [Ocramius/ProxyManager](https://github.com/Ocramius/ProxyManager) library. This library is not installed by default with PHP-DI, you need to require it:

````
composer require ocramius/proxy-manager
````

### PHP configuration file

```php
<?php

return [
    'foo' => DI\create('MyClass')
        ->lazy(),
];
```

### Annotations

```php
/**
 * @Injectable(lazy=true)
 */
class MyClass
{
}
```

### PHP code

```php
<?php
$containerPHP->set('foo', \DI\create('MyClass')->lazy());
```

## When to use

Lazy injection requires to create proxy objects for each object you declare as `lazy`. It is not recommended to use this feature more that a few times in one application.

While proxies are extremely optimized, they are only worth it if the object you define as lazy has a constructor that takes some time (e.g. connects to a database, writes to a file, etc.).

## Optimizing performances

PHP-DI needs to generate proxies of the classes you mark as "*lazy*".

By default those proxies are generated on every HTTP request, this is good for development but not for production.

In production you should generate proxies to file:

```php
// Enable writing proxies to file in the tmp/proxies directory
$containerBuilder->writeProxiesToFile(true, __DIR__ . '/tmp/proxies');
```

You will need to clear the directory every time you deploy to avoid keeping outdated proxies.

### Generating the proxy classes on container build time

By default the proxies are written to disk the first time they are required. Enabling pre-generation will write the proxy classes to disk when the container is built.

```php
// Enable writing proxies to file in the var/cache directory at container compile time
$containerBuilder->enableCompilation(__DIR__ . '/var/cache');
$containerBuilder->writeProxiesToFile(true, __DIR__ . '/var/cache');
``` 

For this functionality to work, both configuration options have to be set. 