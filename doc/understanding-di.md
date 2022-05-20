---
layout: documentation
current_menu: understanding-di
---

# Understanding Dependency Injection

*Dependency injection* and *dependency injection containers* are different things:

- **dependency injection is a method** for writing better code
- **a container is a tool** to help injecting dependencies

You don't *need* a container to do dependency injection. However a container can help you.

PHP-DI is about this: making dependency injection more practical.


## The theory

### Classic PHP code

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

### Using dependency injection

Here is how a code using DI will roughly work:

* Application needs Foo, which needs Bar, which needs Bim, so:
* Application creates Bim
* Application creates Bar and gives it Bim
* Application creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

This is the pattern of **Inversion of Control**. The control of the dependencies is **inverted** from one being called to the one calling.

The main advantage: the one at the top of the caller chain is always **you**. You can control all dependencies and have complete control over how your application works. You can replace a dependency by another (one you made for example).

For example what if Library X uses Logger Y and you want to make it use your logger Z? With dependency injection, you don't have to change the code of Library X.

### Using a container

Now how does a code using PHP-DI works:

* Application needs Foo so:
* Application gets Foo from the Container, so:
    * Container creates Bim
    * Container creates Bar and gives it Bim
    * Container creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

In short, **the container takes away all the work of creating and injecting dependencies**.


## Understanding with an example

This is a real life example comparing a classic implementation (using `new` or singletons) VS using dependency injection.

### Without dependency injection

Say you have:

```php
class GoogleMaps
{
    public function getCoordinatesFromAddress($address) {
        // calls Google Maps webservice
    }
}
class OpenStreetMap
{
    public function getCoordinatesFromAddress($address) {
        // calls OpenStreetMap webservice
    }
}
```

The classic way of doing things is:

```php
class StoreService
{
    public function getStoreCoordinates($store) {
        $geolocationService = new GoogleMaps();
        // or $geolocationService = GoogleMaps::getInstance() if you use singletons

        return $geolocationService->getCoordinatesFromAddress($store->getAddress());
    }
}
```

Now we want to use the `OpenStreetMap` instead of `GoogleMaps`, how do we do?
We have to change the code of `StoreService`, and all the other classes that use `GoogleMaps`.

**Without dependency injection, your classes are tightly coupled to their dependencies.**

### With dependency injection

The `StoreService` now uses dependency injection:

```php
class StoreService {
    private $geolocationService;

    public function __construct(GeolocationService $geolocationService) {
        $this->geolocationService = $geolocationService;
    }

    public function getStoreCoordinates($store) {
        return $this->geolocationService->getCoordinatesFromAddress($store->getAddress());
    }
}
```

And the services are defined using an interface:

```php
interface GeolocationService {
    public function getCoordinatesFromAddress($address);
}

class GoogleMaps implements GeolocationService { ...

class OpenStreetMap implements GeolocationService { ...
```

Now, it is for the user of the StoreService to decide which implementation to use. And it can be changed anytime, without
having to rewrite the `StoreService`.

**The `StoreService` is no longer tightly coupled to its dependency.**

## With PHP-DI

You may see that dependency injection has one drawback: you now have to handle injecting dependencies.

That's where a container, and specifically PHP-DI, can help you.

Instead of writing:

```php
$geolocationService = new GoogleMaps();
$storeService = new StoreService($geolocationService);
```

You can write:

```php
$storeService = $container->get('StoreService');
```

and configure which GeolocationService PHP-DI should automatically inject in StoreService through configuration:

```php
$container->set('StoreService', \DI\create('GoogleMaps'));
```

If you change your mind, there's just one line of configuration to change now.

Interested? Go ahead and read the [Getting started](getting-started.md) guide!
