---
layout: documentation
current_menu: scopes
---

# Scopes

**Scopes have been removed in PHP-DI 6.** Read below for more explanations. From now on, all definitions are resolved once and their result is kept during the life of the container (i.e. what was called the `singleton` scope).

Scopes were used to make the container work as a factory: instead of using scopes you can either:

- use the [`Container::make()` method](container.md#make),
- or inject a proper factory object and create the objects you need on demand.

Below is an example of writing a factory object and injecting it.

Before:

```php
return [
    Form::class => create()
        ->scope(Scope::PROTOTYPE), // a new form is created every time it is injected
];

class Service
{
    public function __construct(Form $form)
    {
        $this->form = $form;
    }
}
```

After:

```php
return [
    FormFactory::class => create(),
];

class Service
{
    public function __construct(FormFactory $formFactory)
    {
        $this->form = $formFactory->createForm();
    }
}
```

or:

```php
class Service
{
    public function __construct()
    {
        $this->form = new Form(/* parameters */);
    }
}
```

or you can also inject the container and use it explicitly as a factory (type-hint against `DI\FactoryInterface` to avoid being coupled to the container):

```php
class Service
{
    public function __construct(\DI\FactoryInterface $factory)
    {
        $this->form = $factory->make(Form::class, /* parameters */);
    }
}
```

Scopes also created an illusion that some values could be recalculated on demand. For example you could imagine a factory that returns the current value of an environment variable:

```php
return [
    'config' => factory(function () {
        return getenv('CONFIG_VAR');
    })->scope(Scope::PROTOTYPE),
    
    Service1::class => create()
        ->constructor(get('config')),
    Service2::class => create()
        ->constructor(get('config')),
];
```

Contrary to what one could think, if the `CONFIG_VAR` changes it will not be updated in places were it has already been injected before the change. Scopes are not a solution for values that can change during execution, yet they could be misinterpreted as such a solution.
