# Configuration file

The configuration file is **optional**. PHP-DI will work with default behavior without it.

Here is an example of a configuration file (`di.ini`):

```ini
; PHP-DI - Dependency injection configuration

; Value injections
di.values["db.host"] = "localhost"
di.values["email.from"] = "support@example.com"

; Type mapping for injection using abstract types
di.types.map["\My\Interface"] = "\My\Implementation"
di.types.map["\My\AbstractClass"] = "\My\OtherImplementation"
```

To import the configuration file:

```php
\DI\Container::getInstance()->addConfigurationFile('di.ini');
```