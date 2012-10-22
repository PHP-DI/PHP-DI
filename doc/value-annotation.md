# @Value annotation

PHP-DI offers `@Value` annotations to inject values:

```php
<?php
use DI\Annotations\Value;

class MyClass {
    /**
     * @Value("db.host")
     */
    private $dbHost;

    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
    }
}
```

You can use a `.ini` file to configure the values to inject:

```ini
di.values["db.host"] = "localhost"
```

To import the configuration file:

```php
\DI\Container::getInstance()->addConfigurationFile('di.ini');
```

Read more about the [configuration file](doc/configuration-file).