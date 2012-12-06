Here is real life example, comparing the classic implementation (`new`, singletons...)
vs an implementation using dependency injection.

## Classic implementation

Say you have:

```php
class GoogleMapsService {
    public function getCoordinatesFromAddress($address) {
        // calls Google Maps webservice
    }
}
class OpenStreetMapService {
    public function getCoordinatesFromAddress($address) {
        // calls OpenStreetMap webservice
    }
}
```

The classic way of doing things is:

```php
class StoreService {
    public function getStoreCoordinates($store) {
        $geolocationService = new GoogleMapsService();
        // or $geolocationService = GoogleMapsService::getInstance() if you use singletons
        return $geolocationService->getCoordinatesFromAddress($store->getAddress());
    }
}
```

Now we want to use the OpenStreetMapService instead of GoogleMapsService,
how do we do? We have to change the code of StoreService, and all the other classes that use GoogleMapsService.

**Without dependency injection, your classes are tightly coupled with their dependencies.**

## Dependency injection implementation

The StoreService now uses dependency injection:

```php
class StoreService {
    /**
     * @Inject
     * @var GeolocationService
     */
    private $geolocationService;

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
class GoogleMapsService implements GeolocationService {
    public function getCoordinatesFromAddress($address) {
        // calls Google Maps webservice
    }
}
class OpenStreetMapService implements GeolocationService {
    public function getCoordinatesFromAddress($address) {
        // calls OpenStreetMap webservice
    }
}
```

You then configure which implementation will be used:

```php
Container::addConfiguration([
    "aliases" => [
        "GeolocationService" => "OpenStreetMapService",
    ],
]);
```

If you change your mind, there's just one line of configuration to change now.
