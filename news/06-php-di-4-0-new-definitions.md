# What will change in PHP-DI 4: the new definition format

*Posted by [Matthieu Napoli](http://mnapoli.fr) on January 16th 2014*

In PHP-DI 4, the definition format will change. Autowiring and Annotations are still there, but the YAML
and PHP array definitions have disappeared. Let's see why.

## Why was YAML a bad choice

The recommended definition format for PHP-DI 3.x was YAML. Everybody loves YAML. This is a clean, short and readable format.

I started to question this choice though. There have always been limitations to using YAML, the main one would be that
it's not PHP code:

- you cannot use constants, or class constants
- you cannot write simple PHP code like concatenating strings, or adding numbers…
- you cannot use anonymous functions to define entries/services
- no autocompletion support (on class names for example) or Ctrl+click to go to the class
- no refactoring support (renaming a class for example)
- you cannot have helpers (classes, functions, …) to help you write the configuration

And it all became very obvious when Symfony introduced their new component: [ExpressionLanguage](http://symfony.com/doc/current/components/expression_language/introduction.html).

ExpressionLanguage is a language that looks like PHP, except you use `.` instead of `->` and variables don't have the `$` prefix.
And with that component, you are now able to write code in Symfony's container configuration. Isn't it great?

Well, even though the component itself is very good, it really shows how stupid the situation is (not Symfony!).

**If we need to configure our PHP app, and if for this we need to use code, then let's use PHP!**

### PHP 5.4, 5.5 and 5.6 to the rescue

Moreover, since PHP 5.3, there has been very nice features added to PHP that make the code much more concise, even more than YAML sometimes.

Here is a YAML configuration:

```yaml
Acme\BlogModule\Model\ArticleRepository:
    scope: prototype
    constructor: [Doctrine\ORM\EntityManager, some.param]
```

Here would be the PHP 5.3 version:

```php
return array(
    'Acme\BlogModule\Model\ArticleRepository' => array(
        'scope' => Scope::PROTOTYPE(),
        'constructor' => array('Doctrine\ORM\EntityManager', 'some.param'),
    ),
);
```

Pretty verbose! But here is the PHP 5.4 version:

```php
return [
    'Acme\BlogModule\Model\ArticleRepository' => [
        'scope' => Scope::PROTOTYPE(),
        'constructor' => ['Doctrine\ORM\EntityManager', 'some.param'],
    ],
];
```

Short arrays make it much better. Let's not stop here, and use PHP 5.5:

```php
use ...;
return [
    ArticleRepository::class => [
        'scope' => Scope::PROTOTYPE(),
        'constructor' => [EntityManager::class, 'some.param'],
    ],
];
```

It's getting very nice. Even nicer than YAML, because here the IDE can recognize the class and give us Ctrl+Click and
refactoring support.

And when we think about PHP 5.6, what new syntactic sugar will we get? [Use function](https://wiki.php.net/rfc/use_function).
Keep that in mind, it's going to come up again later.

## Why is the current PHP format not a good choice still

OK, we've decided that YAML may not be appropriate. Why not just use the PHP equivalent?

The current format (YAML or PHP) is actually a data structure, an array. Here, it is obvious:

```php
return [
    ArticleRepository::class => [
        'scope' => Scope::PROTOTYPE(),
        'constructor' => [EntityManager::class, 'some.param'],
    ],
];
```

What if you mistype `constructor`? Your IDE will not tell you you've made a mistake, and are you sure PHP-DI will warn you?
And what if you don't remember whether it's `constructor`, `__construct` or `arguments` (like in Symfony)?
And mostly, what is the difference between defining a service (using an array, as shown above) and a value that **is** an array?

There are a lot of loopholes with this format, and many of them were already known (reported in GitHub tickets).

**This is not practical!** As a user:

- I don't want to learn a format/syntax for each DI container
- I don't want ambiguity
- I don't want to make mistakes

Arbitrary array structures are ambiguous and confusing, you've probably met this problem before in other contexts.
And what do you do in that case? You use **OOP** and you get **explicit naming**, **autocompletion** and **strict validation**.

```
// This is an example, not the real format
$definition = new ObjectDefinition();    // this is explicit on what it is, this is not a value, this is an object
$definition->hasScope(Scope::Prototype); // you can't put an invalid scope in there
$definition->withConstructorArguments(
    new InjectOtherContainerEntry(EntityManager::class), // explicit as hell
    new InjectSomeValue('some.param'),
);
$definition->// autocompletion power! no need to learn or read the documentation!
return [
    ArticleRepository::class => $definition,
];
```

Well, that seems nice! It's a bit verbose though!

Here was the first version I came up with (the definition is inlined in the array):

```
return [
    ArticleRepository::class => Entry::object()
        ->withScope(Scope::PROTOTYPE())
        ->withConstructor(Entry::link(EntityManager::class), 'some.param'),
];
```

Nice. Though, do you remember what I said about PHP 5.6 and `use function`, so let's take advantage of that.
Here is what the actual PHP-DI 4.0 format looks like:

```
return [
    ArticleRepository::class => object()
        ->scope(Scope::PROTOTYPE())
        ->constructor(link(EntityManager::class), 'some.param'),
];
```

`object()` and `link()` are actually functions (that return an object helper) in the `DI` namespace.
So in this example, the `use` at the beginning of the file are skipped, but here is what it would look like:

```php
use Acme\BlogModule\Model\ArticleRepository;
use Doctrine\ORM\EntityManager;
use function DI\object;
use function DI\link;
```

## Not bulletproof, not perfect

What about a complex example:

```
return [
    ArticleRepositoryInterface::class => object(ArticleRepository::class)
        ->constructor(link(EntityManager::class), link(PrivateSubDependency::class), 'some.param')
        ->method('setFoo', link(Foo::class))
        ->method('setBar', link(Bar::class))
        ->method('configureStuff', 'localhost', 8080)
        ->property('logger', link(LoggerInterface::class)),
    PrivateSubDependency::class => object()
        ->constructor(PrivateSubDependency::MY_CONSTANT),
];
```

That's doesn't look really nice…

Some containers come with bridges/adapters/bundles that abstract a bit the code for certain packages
(think of [Symfony's configuration for Doctrine](http://symfony.com/fr/doc/current/reference/configuration/doctrine.html)).
That may seem like a good idea at first, but when you find out that all Doctrine's documentation is useless (because abstracted),
you begin to wonder: what's the point of reading Doctrine's documentation! And what if my specific config isn't supported
by the bridge/adapter/bundle? Or isn't documented?

It turns out there are [so](http://stackoverflow.com/questions/12702657/how-to-configure-naming-strategy-in-doctrine-2)
[many](http://stackoverflow.com/questions/16600028/how-to-connect-to-mysql-using-ssl-on-symfony-doctrine)
[people](http://stackoverflow.com/questions/9468793/how-to-configure-doctrine-in-symfony2)
[lost](http://stackoverflow.com/questions/18503093/how-do-i-change-symfony-2-doctrine-mapper-to-use-my-custom-directory-instead-of)
[because](http://stackoverflow.com/questions/16854148/how-to-make-symfony2-dic-to-call-doctrine-orm-configurationsethydrationcacheimp)
[of this](http://stackoverflow.com/questions/12935829/configuring-the-translatable-doctrine2-extension-with-symfony2-using-yaml).

Let's stop pretending and **write some damn PHP**:

```
return [
    ArticleRepositoryInterface::class => factory(function (Container $c) {
        $dependency = new PrivateSubDependency(PrivateSubDependency::MY_CONSTANT);

        $repository = new ArticleRepository($c->get(EntityManager::class), $dependency, 'some.param');
        $repository->setFoo($c->get(Foo::class));
        $repository->setBar($c->get(Bar::class));
        $repository->configureStuff('localhost', 8080);
        $repository->logger = $c->get(LoggerInterface::class);

        return $repository;
    }),
];
```

THAT is readable. You can't have it more readable and maintainable for PHP developers.

## Conclusion

In PHP-DI 4, you can have:

- a nice, explicit API for defining entries easily
- complex definitions as PHP code (using closures)

The first one works great in combination with autowiring and annotations for example, to bind interfaces to classes,
or set a value for non-object parameters (which cannot be done using autowiring alone).

The second one is for when the definition is a bit more complex.

**PHP-DI 4 is not released yet**. You can however try the [4.0.0-beta2 version](https://github.com/mnapoli/PHP-DI/releases/tag/4.0.0-beta2)
or follow the [4.0 pull request](https://github.com/mnapoli/PHP-DI/pull/119).
