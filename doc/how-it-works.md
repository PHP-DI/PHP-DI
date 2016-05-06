---
layout: documentation
current_menu: how-it-works
---

# How PHP-DI works

Do you want to help out improving PHP-DI? Or are you simply curious? Here is a short presentation of how PHP-DI works.

## Global architecture

The main component is the `Container` class. It is created by a `ContainerBuilder`, which is just a helper class.

It is the entry point from the user's point of view, it is also the component that coordinates all other sub-components.

Its main role is to return **entries** by their **entry name**:

```php
$entry = $container->get('entryName');
```

A container instance has the following sub-components:

- a `DefinitionSource` that returns a `Definition` for an entry name (by looking in several sub-definition sources)
- a list of `DefinitionResolver` that take a `Definition` and resolve it to a value (f.e. if it's an object, it will create it)

### Definitions

A definition defines what an entry is:

- **a simple value** (string, number, object instance…): `ValueDefinition`
- **a factory/callable returning the value**: `FactoryDefinition`
- **a definition of an entry alias**: `AliasDefinition`
- **a definition of a class**: `ObjectDefinition`
- **a definition of an environment variable**: `EnvironmentVariableDefinition`

The class definition describes how the container should create a class instance (what parameters the constructor takes, …).
