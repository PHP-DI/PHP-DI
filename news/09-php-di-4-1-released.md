---
template: blogpost
title: PHP-DI 4.1 released
author: Matthieu Napoli
date: April 16th 2014
---

I am happy to announce that PHP-DI version 4.1 has been released.

This version was focused on stability and documentation improvements, with full backward compatibility.


## Container-Interop 1.0 compatibility

If you haven't heard of [container-interop](https://github.com/container-interop/container-interop)
yet, it's a project that tries to improve interoperability between containers and frameworks.
It is inspired by PSRs from the PHP-FIG (and hopefully will become a PSR some day).

The first release introduced a `ContainerInterface` that was exactly the same as
PHP-DI's `ContainerInterface` introduced in v4.0. That was of course planned, so that from v4.1 and up
`DI\ContainerInterface` is deprecated in favor of
[`Interop\Container\ContainerInterface`](https://github.com/container-interop/container-interop/blob/master/src/Interop/Container/ContainerInterface.php):

```php
namespace Interop\Container;

interface ContainerInterface
{
    public function get($id);

    public function has($id);
}
```

PHP-DI's interface now extends this standard interface, which means there is no BC-break and code using
`DI\ContainerInterface` still works.

However, if you use `Interop\Container\ContainerInterface` when using the container **your code will not
be coupled to PHP-DI**. That is extremely good news: it will allow you (or your framework) to switch
the container and use another library anytime without impact on your codebase (except your container
configuration of course).

This is a very similar situation to PSR-3 and the `LoggerInterface` it you have ever used it.


## Better exception messages

Errors when using a DI container are embarrassing: given you never (should) use the container directly,
you never expect an exception from the container. It always comes out of nowhere with cryptic messages
and it takes you several seconds to understand what's happening.

To ease this, exception messages have been improved, especially when there is an error while building
an object.

Now the whole configuration for the object will be dumped in the exception message, which makes it much
easier to debug. Here is an example:

```
Entry Acme\Store\ProductServiceInterface cannot be resolved: The parameter 'mailer' of Acme\Store\ProductService::__construct has no value defined or guessable

Definition of Acme\Store\ProductServiceInterface:
Object (
    class = Acme\Store\ProductService
    scope = singleton
    lazy = false
    __construct(
        $logger = link(Psr\Log\LoggerInterface)
        $mailer = #UNDEFINED#
    )
)
```

Now you can clearly see that the `$logger` parameter was correctly defined (or recognized)
but the `$mailer` parameter wasn't.


## Better Symfony 2 documentation

PHP-DI 4.0 works perfectly in Symfony 2 but the documentation about this integration was not really extensive.

This has been worked upon so that you can feel much more confident trying PHP-DI in Symfony.

[Have a look at the documentation](http://php-di.org/doc/frameworks/symfony2.html)


## HHVM support

[HHVM](http://hhvm.com) is the new cool kid in town: it runs PHP much faster than the classic
PHP Zend Engine.

Starting from version 4.1, PHP-DI's tests are all green on HHVM. That doesn't necessarily means that
there is 100% certainty that there is no bug, but it means that any bug found while running
in HHVM will be fixed.

In short: **HHVM is officially supported**.

What about [Hack](http://hacklang.org)? HHVM support doesn't mean Hack support.
I honestly have no idea how PHP-DI behaves with Hack code but I guess it might not work.
If you are interested in working on this, get in touch!


## Change log

You can also read the complete [change log](../change-log.md).
