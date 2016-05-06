---
layout: documentation
current_menu: zf2
---

# PHP-DI in Zend Framework 2

If you are using Zend Framework 2, PHP-DI provides an easy and clean integration with the existing framework's container.

## Set up

First, install the bridge:

```
composer require php-di/zf2-bridge
```

Register it in `application_root/config/application.config.php`:

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

If you want to use annotations, please read the "Configuration" section below.

## Usage

Now you can inject dependencies in your controllers.

Here is an example of the GuestbookController of the quickstart (using annotations):

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

If you'd like to define injections using a configuration file, put them in `application_root/config/php-di.config.php`:

```
<?php
return [
    'Application\Service\GreetingServiceInterface' => DI\object('Application\Service\GreetingService'),
];
```

Head over to [the definitions documentation](../php-definitions.html) if needed.

## Configuration

To configure PHP-DI itself, you have to override the module config in `config/autoload/global.php` or `config/autoload/local.php`:

```php
return [
    'phpdi-zf2' => [
        ...
    ]
];
```

### Enable or disable annotations

Annotations are disabled by default since PHP-DI 5. To enable them, use the following config:

```php
return [
    'phpdi-zf2' => [
        'useAnntotations' => true,
    ]
];
```

### Override definitions file location

```php
return [
    'phpdi-zf2' => [
        'definitionsFile' => __DIR__ . '/../my-custom-config-file.php',
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

## More

Read more on the [ZF2-Bridge project on GitHub](https://github.com/PHP-DI/ZF2-Bridge).
