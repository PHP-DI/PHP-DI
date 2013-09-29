# How PHP-DI works

Do you want to help out improving PHP-DI? Or are you simply curious? Here is a short presentation of how PHP-DI works.

## Global architecture

The main component is the `Container` class.

It is the entry point from the user's point of view, it is also the component that coordinates all other sub-components.

Its main role is to return **entries** by their **entry name**:

```php
$entry = $container->get('entryName');
```

A container instance has the following sub-components:

- a `DefinitionManager` that returns a `Definition` for an entry name
- a `Factory` to create instances from the definitions

### Definitions

A definition defines what is an entry:

- **a simple value** (string, number, object instance…): `ValueDefinition`
- **a closure returning the value**: `ClosureDefinition`
- **a definition of a class**: `ClassDefinition`

The last type (class definition) describes how the container should create a class instance (what parameters the constructor takes, …).

### Definition sources

All definition sources implement the `DefinitionSource` interface.

Their role is to return a `Definition` for an entry name.

Implementations are:

- `SimpleDefinitionSource`, contains definitions in an array in memory (for definitions created on the fly)
- `ArrayDefinitionSource` parses definitions from a PHP array
- `ReflectionDefinitionSource` uses [PHP reflection](http://fr.php.net/manual/en/book.reflection.php) to guess constructors parameters
- `AnnotationDefinitionSource` parses code annotations using [Doctrine annotations library](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html)
- `CombinedDefinitionSource` wraps a list of other definition sources and merges definitions from them

### File loaders

File loaders extend the `DefinitionFileLoader` abstract class.

Their goal is to read a file containing definitions (PHP, YAML, …) and return a PHP array that will be read by the `ArrayDefinitionSource`.

### Definition manager

The `DefinitionManager` sits on top of the definition sources to manage them.

The container uses a definition manager to add sources and to get a `Definition` for an entry name.

It answers the following needs:

- prioritize correctly the sources (e.g. annotations should override reflection): read more about [definition overriding](definition-overriding.md)
- cache the definitions (if configured)

*This could be improved by splitting this into 2 separate classes to follow the [Single Responsibility Principle](http://en.wikipedia.org/wiki/Single_responsibility_principle).*

### Factory

The role of the `Factory` is to return an object instance from a `ClassDefinition`.
