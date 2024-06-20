---
layout: documentation
current_menu: autowiring
---

# Autowiring

*Autowiring* is an exotic word that represents something very simple: **the ability of the container to automatically create and inject dependencies**.

In order to achieve that, PHP-DI uses [PHP's reflection](http://php.net/manual/book.reflection.php) to detect what parameters a constructor needs.

Autowiring does not affect performance when [compiling the container](performances.md).

Let's take this example:

```php
class UserRepository
{
    // ...
}

class UserRegistrationService
{
    public function __construct(UserRepository $repository)
    {
        // ...
    }
}
```

When PHP-DI needs to create the `UserRegistrationService`, it detects that the constructor takes a `UserRepository` object (using the [type declarations](http://www.php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration)).

**Without any configuration**, PHP-DI will create a `UserRepository` instance (if it wasn't already created) and pass it as a constructor parameter. The equivalent raw PHP code would be:

```php
$repository = new UserRepository();
$service = new UserRegistrationService($repository);
```

As you can imagine, it's very simple, doesn't require any configuration, and it just works!

## Configuration

**Autowiring is enabled by default.** You can disable it using the container builder:

```php
$containerBuilder->useAutowiring(false);
```

It may be helpful not to disable autowiring but have logs about autowiring made a hit. You can enable logging using the container builder:

```php
// of course, you can use any other PSR-3 logger, Monolog is just an example
$logger = new \Monolog\Logger('php-di');

$containerBuilder->setLogger($logger, \Psr\Log\LogLevel::INFO);
```

## Limitations

PHP-DI won't be able to resolve cases like this:

```php
class Database
{
    public function __construct($dbHost, $dbPort)
    {
        // ...
    }

    public function setLogger(LoggerInterface $logger)
    {
        // ...
    }
}
```

- it will not know what parameters to give to the constructor (since there is no type-hinting for an object)
- `setLogger()` will not be called

For those classes, you will need to use `DI\autowire()` in [PHP definitions](php-definitions.md) to declare explicitly what to inject.

PHP-DI is also unable to use Reflection on any PHP-internal classes like such that provide access to extension capabilities.
Examples include `Memcached`, `Gearman`, any `Curl*` classes, anything from the SPL classes like `RecursiveIteratorIterator`, and so on.
If you want to instantiate any class provided by the PHP engine or an extension, you have to provide explicit constructor parameters to PHP-DI.
