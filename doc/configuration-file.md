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

## Value injection

```ini
di.values["key"] = "value"
```

Defines a value that can be injected using the [@Value annotation](doc/value-annotation).

## Type mapping

```ini
di.types.map["\My\Interface"] = "\My\Implementation"
```

If you are trying to inject an abstract type (interface or abstract class),
this configuration allows you to define which implementation to use.

You can also use it to map any types, i.e. to override a type being injected.
