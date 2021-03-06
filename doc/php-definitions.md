---
layout: documentation
current_menu: php-definitions
---

# PHP definitions

On top of [autowiring](autowiring.md) and [annotations](annotations.md), you can use **a PHP configuration format** to define injections.

You can register that configuration as an array:

```php
$containerBuilder->addDefinitions([
    // place your definitions here
]);
```

Or by putting it into a file returning an array:

```php
<?php
return [
    // place your definitions here
];
```

```php
$containerBuilder->addDefinitions('config.php');
```

## A word about lazy loading

PHP-DI loads the definitions you have written and uses them like instructions on how to create objects.

However those objects are only created when/if they are requested from the container, for example through `$container->get(…)` or when they need to be injected in another object. That means you can have a large amount of definitions, PHP-DI will not create all the objects unless asked to.

The only exception to that behavior is if you define *objects* as [values](#values) but this is not recommended (this is explained in the ["Values" section](#values)).

## Syntax

PHP-DI's definitions are written using a *DSL* (Domain Specific Language) written in PHP and based on helper functions.

All the examples shown in this page are using a PHP 7.0 compatible syntax. You are encouraged to use the following features:

- PHP 5.5 `::class` magic constant:

    ```php
    use Psr\Log\LoggerInterface;
    use Monolog\Logger;

    return [
        LoggerInterface::class => DI\create(Logger::class)
    ];
    ```
- PHP 5.6 function import:

    ```php
    use function DI\create;
    use function DI\get;

    return [
        'Foo' => create()
            ->constructor(get('Bar')),
    ];
    ```

*Watch out:* remember the helper functions (`DI\create()` for example) are namespaced functions, not classes. Do not use `new` (e.g. `new DI\create()`) or you will get a fatal error "Class 'DI\create' not found".

## Definition types

This definition format is the most powerful of all. There are several kind of **entries** you can define:

- **[values](#values)**
- **[factories](#factories)**
- **[objects](#objects)**
- **[autowired objects](#autowired-objects)**
- **[aliases](#aliases)**
- **[environment variables](#environment-variables)**
- **[string expressions](#string-expressions)**
- **[arrays](#arrays)**
- **[wildcards](#wildcards)**

### Values

Values (aka *parameters* in Symfony) are simple PHP values.

```php
return [
    'database.host'     => 'localhost',
    'database.port'     => 5000,
    'report.recipients' => [
        'bob@example.com',
        'alice@example.com',
    ],
];
```

You can also define object entries by creating them directly:

```php
return [
    'Foo' => new Foo(),
];
```

However **this is not recommended** as that object will be created *for every PHP request*, even if not used (it will not be lazy loaded like explained at the top of this section). It will also prevent the container from being compiled.

You should instead use one of the methods documented below.

### Factories

Factories are **PHP callables** that return the instance. They allow to easily define objects *lazily*, i.e. each object will be created only when actually needed (because the callable will be called when actually needed).

Just like any other definition, factories are called once and the same result is returned every time the factory needs to be resolved.

Factories can be defined using the `DI\factory()` helper, however it is possible to use a closure as a shortcut:

```php
use Psr\Container\ContainerInterface;
use function DI\factory;

return [
    'Foo' => function (ContainerInterface $c) {
        return new Foo($c->get('db.host'));
    },
    
    // Same as
    'Foo' => DI\factory(function (ContainerInterface $c) {
        return new Foo($c->get('db.host'));
    }),
];
```

Other services can be injected via type-hinting (as long as they are registered in the container or autowiring is enabled):

```php
return [
    'LoggerInterface' => DI\create('MyLogger'),

    'Foo' => function (LoggerInterface $logger) {
        return new Foo($logger);
    },
];
```

The `DI\factory()` helper provides a `parameter()` method to allow you to specify entries, like values, that can't be automatically injected via type-hinting.

```php
return [
    'Database' => DI\factory(function ($host) {...})
        ->parameter('host', DI\get('db.host')),
];
```

This can also be done by injecting the container itself, as seen in the first example. When injecting the container, you should type-hint against the interface `Psr\Container\ContainerInterface` instead of the implementation `DI\Container`.

Factories can be any PHP callable, so they can also be class methods:

```php
class FooFactory
{
    public function create(Bar $bar)
    {
        return new Foo($bar);
    }
}
```

You can choose to eagerly load `FooFactory` at definition time:

```php
return [
    // not recommended!
    Foo::class => DI\factory([new FooFactory, 'create']),
];
```

but the factory will be created on every request (`new FooFactory`) even if not used. Additionally with this method it's harder to pass dependencies in the factory.

The recommended solution is to let the container create the factory:

```php
return [
    Foo::class => DI\factory([FooFactory::class, 'create']),
    // alternative syntax:
    Foo::class => DI\factory('Namespace\To\FooFactory::create'),
];
```

The configuration above is equivalent to the following code:

```php
$factory = $container->get(FooFactory::class);
return $factory->create(...);
```

If the factory is a static method, it's just as simple:


```php
class FooFactory
{
    public static function create()
    {
        return ...
    }
}

return [
    Foo::class => DI\factory([FooFactory::class, 'create']),
];
```

Please note:

- `factory([FooFactory::class, 'build'])`: if `build()` is a **static** method then the object will not be created: `FooFactory::build()` will be called statically (as one would expect)
- you can set any container entry name in the array, e.g. `DI\factory(['foo_bar_baz', 'build'])` (or alternatively: `DI\factory('foo_bar_baz::build')`), allowing you to configure `foo_bar_baz` and its dependencies like any other object
- as a factory can be any PHP callable, you can use invokable objects, too: `DI\factory(InvokableFooFactory::class)` (or alternatively: `DI\factory('invokable_foo_factory')`, if it's defined in the container)
- all closures will be considered by PHP-DI as *factories*, even if they are nested into other definitions like `create()`, `env()`, etc. (read more in the [Nesting definitions](#nesting-definitions) section)

#### Retrieving the name of the requested entry

If you want to reuse the same factory for creating different entries, you might want to retrieve the name of the entry that is currently being resolved. You can do this by injecting the `DI\Factory\RequestedEntry` object using a type-hint:

```php
use DI\Factory\RequestedEntry;

return [
    'Foo' => function (RequestedEntry $entry) {
        // $entry->getName() contains the requested name
        $class = $entry->getName();
        return new $class();
    },
];
```

Since `RequestedEntry` is injected using the type-hint, you can combine it with injecting the container or any other service. The order of factory arguments doesn't matter.

#### Decoration

When using a system with multiple definition files, you can override a previous entry using a decorator:

```php
return [
    // decorate an entry previously defined in another file
    'WebserviceApi' => DI\decorate(function ($previous, ContainerInterface $c) {
        return new CachedApi($previous, $c->get('cache'));
    }),
];
```

Please read the [definition overriding guide](definition-overriding.md) to learn more about this.

### Objects

Using factories to create object is very powerful (as we can do anything using PHP), but the `DI\create()` helper can sometimes be simpler. Some examples:

```php
return [
    // instantiate the Logger class to create the object
    'Logger' => DI\create(),
    // mapping an interface to an implementation
    'LoggerInterface' => DI\create('MyLogger'),
    // using an arbitrary name for the entry
    'logger.for.backend' => DI\create('Logger'),
];
```

The `DI\create()` helper lets you define constructor parameters:

```php
return [
    'Logger' => DI\create()
        ->constructor('app.log', DI\get('log.level'), DI\get('FileWriter')),
];
```

As well as setter/method injections:

```php
return [
    'Database' => DI\create()
        ->method('setLogger', DI\get('Logger')),
    // you can call a method twice
    'Logger' => DI\create()
        ->method('addBackend', 'file')
        ->method('addBackend', 'syslog'),
];
```

And property injections:

```php
return [
    'Foo' => DI\create()
        ->property('bar', DI\get('Bar')),
];
```

Each entry will be resolved once and the same instance will be injected everywhere it is used. Just like with other definitions, objects defined with the `create()` helper are created only when needed (lazy-loading).

### Autowired objects

**If you have enabled [autowiring](autowiring.md)** you can use the `DI\autowire()` helper to customize how objects will be autowired.

`DI\autowire()` behaves like `DI\create()` excepts instead of configuring from scratch how the object will be built, we only override what we need from the autowiring.

```php
return [
    // when using no option it is not necessary to write it in the config file
    'MyLogger' => DI\autowire(),
    
    // mapping an interface to an implementation (autowire the MyLogger class)
    'LoggerInterface' => DI\autowire('MyLogger'),
    
    // using an arbitrary name for the entry
    'logger.for.backend' => DI\autowire('MyLogger'),
];
```

Like `DI\create()`, you can explicitly set constructor parameters:

```php
return [
    'Logger' => DI\autowire()
        ->constructor('app.log', DI\get('log.level'), DI\get('FileWriter')),
];
```

As well as setter/method injections:

```php
return [
    'Database' => DI\autowire()
        ->method('setLogger', DI\get('Logger')),
];
```

And property injections:

```php
return [
    'Foo' => DI\autowire()
        ->property('bar', DI\get('Bar')),
];
```

You can also define only specific parameters: it allows to define the parameters that couldn't be guessed by the autowiring using type-hints.

```php
return [
    'Logger' => DI\autowire()
        // set the $filename parameter
        ->constructorParameter('filename', 'app.log')
        // set the $handler parameter
        ->methodParameter('setHandler', 'handler', DI\get('SyslogHandler')),
];
```

Since PHP 8, you can also use [named arguments](https://stitcher.io/blog/php-8-named-arguments):

```php
return [
    'Logger' => DI\autowire()
        // set the $filename parameter
        ->constructor(
            filename: 'app.log'
        )
        // set the $handler parameter
        ->method('setHandler', handler: DI\get('SyslogHandler')),
];
```

### Aliases

You can alias an entry to another using the `DI\get()` helper:

```php
return [
    'doctrine.entity_manager' => DI\get('Doctrine\ORM\EntityManager'),
];
```

### Environment variables

You can get an environment variable's value using the `DI\env()` helper:

```php
return [
    'db1.url' => DI\env('DATABASE_URL'),
    // with a default value
    'db2.url' => DI\env('DATABASE_URL', 'postgresql://user:pass@localhost/db'),
    // with a default value that is another entry
    'db2.host' => DI\env('DATABASE_HOST', DI\get('db.host')),
];
```

### String expressions

You can use the `DI\string()` helper to concatenate strings entries:

```php
return [
    'path.tmp' => '/tmp',
    'log.file' => DI\string('{path.tmp}/app.log'),
];
```

### Arrays

Entries can be arrays containing simple values or other entries:

```php
return [
    'report.recipients' => [
        'bob@example.com',
        'alice@example.com',
    ],
    'log.handlers' => [
        DI\get('Monolog\Handler\StreamHandler'),
        DI\get('Monolog\Handler\EmailHandler'),
    ],
];
```

Arrays have additional features if you have multiple definition files: read the [definition overriding](definition-overriding.md) documentation.

### Wildcards

You can use wildcards to define a batch of entries. It can be very useful to bind interfaces to implementations:

```php
return [
    'Blog\Domain\*RepositoryInterface' => DI\create('Blog\Architecture\*DoctrineRepository'),
];
```

In our example, the wildcard will match `Blog\Domain\UserRepositoryInterface`, and it will map it to
`Blog\Architecture\UserDoctrineRepository`.

Good to know:

- the wildcard does not match across namespaces
- an exact match (i.e. without `*`) will always be chosen over a match with a wildcard
(first PHP-DI looks for an exact match, then it searches in the wildcards)
- in case of "conflicts" (i.e. 2 different matches with wildcards), the first match will prevail

### Nesting definitions

You can nest definitions inside others to avoid polluting the container with unnecessary entries. For example:

```php
return [
    'Foo' => DI\create()
        ->constructor(DI\string('{root_directory}/test.json'), DI\create('Bar')),
];
```

Keep in mind that closures are equivalent to "factory" definitions. As such, **closures are always interpreted as factories, even when nested inside other definitions.** If you use anonymous functions for something else than factories you need to wrap them in the `DI\value()` helper:

```php
return [
    'router' => DI\create(Router::class)
        ->method('setErrorHandler', DI\value(function () {
            ...
        })),
];
```

Of course this applies only to closures that are inside your configuration files.

## Setting definitions in the container directly

In addition to defining entries in an array, you can set them directly in the container as shown below.

```php
$container->set('db.host', 'localhost');
$container->set('My\Class', \DI\create()
    ->constructor('some raw value')));
```

**Using array definitions is however recommended since it allows to [compile the container](performances.md).** All entries configured with `Container::set()` will **not** be compiled.

Be also aware that it isn't possible to add definitions to a container on the fly **when using a compiled container**:

```php
$builder = new ContainerBuilder();
$builder->setDefinitionCache(new ApcCache());
$container = $builder->build();

// Works: you can set values
$container->set('foo', 'hello');
$container->set('bar', new MyClass());

// Error: you can't set definitions using ->set() when using a cache
$container->set('foo', DI\create('MyClass'));
```

The reason for this is that definitions are cached (not values). If you set a definition dynamically, then it will be cached, which could lead to very weird bugs (because dynamic definitions should of course not be cached since they are… dynamic).

In that case, put your definitions in an array or file as shown above in this article.
