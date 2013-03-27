Here is real life example, comparing the classic implementation (`new`, singletons...)
vs an implementation using dependency injection.


## Classic implementation

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

**Without dependency injection, your classes are tightly coupled with their dependencies.**


## Dependency injection implementation

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

Now, it is for the user of the StoreService to decide which implementation to use.

**The `StoreService` is no longer tightly coupled with its dependency.**


## PHP-DI

You may also see that dependency injection will leave with one drawback: you now have to handle injecting dependencies.

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
// config/di.php
return [
    'GeolocationService' => [
        'class' => 'GoogleMaps',
    ],
];
```

If you change your mind, there's just one line of configuration to change now.


## Read more

Wait!

What you saw is just a teeny tiny fraction of what PHP-DI can do.

[Read more](doc/README.md)
