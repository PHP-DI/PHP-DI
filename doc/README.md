# Documentation

* [Getting started](doc/getting-started)
* [Inject annotation](doc/inject-annotation) (`@Inject`)
* [Value annotation](doc/value-annotation) (`@Value`)
* [Configuration file](doc/configuration-file)
* [Contribute](doc/contribute)

## Quickstart example

```php
<?php
use DI\Annotations\Inject;

class Class1 {
    /**
     * @Inject
     * @var Class2
     */
    private $class2;

    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
    }
}
```
