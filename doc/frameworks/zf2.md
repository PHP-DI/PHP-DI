---
layout: documentation
---

# PHP-DI in Zend Framework 2

## Set up

If you are using ZF2, PHP-DI provides easy and clean integration so that you don't have
to call the container (thus avoiding the Service Locator pattern).

First, install the bridge to ZF2:

```
composer require php-di/zf2-bridge
```

To use PHP-DI in your ZF2 application, you need to edit `application_root/config/module.config.php`:

```php
    // ...
    'modules' => [
        ...
        'DI\ZendFramework2',
        ...
    ],
    
    'service_manager' => [
        // ...
        'factories' => [
            'DI\Container' => 'DI\ZendFramework2\Service\DIContainerFactory',
        ],
    ],
```

That's it!

Now you dependencies are injected in your controllers!

Since PHP-DI 5 it's necessary to enable [annotations](#annotations) because they are disabled by default.

## Usage

Now you can inject dependencies in your controllers!

For example, here is the GuestbookController of the quickstart:

```php
class GuestbookController extends AbstractActionController
{
    /**
     * This dependency will be injected by PHP-DI
     * @Inject
     * @var \Application\Service\GuestbookService
     */
    private $guestbookService;

    public function indexAction()
    {
        $this->view->entries = $this->guestbookService->getAllEntries();
    }
}
```

If you'd like to specify the di configuration yourself, create this file: `application_root/config/php-di.config.php`
and save it with your PHP DI configuration e.g. like this:

```
return [
    'Application\Service\GreetingServiceInterface' => Di\object('Application\Service\GreetingService'),
];
```

Head over to [PHP-DI's documentation](http://php-di.org/doc/php-definitions.html) if needed.

## Fine tuning

To configure the module, you have to override the module config somewhere at config/autoload/global.php 
or config/autoload/local.php.
  
```php
return [
    'phpdi-zf2' => [
        ...
    ]
];
```

### Override definitions file location

```php
return [
    'phpdi-zf2' => [
        'definitionsFile' => realpath(__DIR__ . '/../my.custom.def.config.php'),
    ]
];
```

### <a name="annotations"></a> Enable or disable annotations

Annotations are disabled by default. To enable them, use the following config:

```php
return [
    'phpdi-zf2' => [
        'useAnntotations' => true,
    ]
];
```

### Enable file cache

```php
return [
    'phpdi-zf2' => [
        'cache' => [
            'adapter' => 'filesystem',
            'namespace' => 'your_di_cache_key',
            'directory' => 'your_cache_directory', // default value is data/php-di/cache
        ],
    ]
];
```

### Enable redis cache

```php
return [
    'phpdi-zf2' => [
        'cache' => [
            'namespace' => 'your_di_cache_key',
            'adapter' => 'redis',
            'host' => 'localhost', // default is localhost
            'port' => 6379, // default is 6379
        ],
    ]
];
```