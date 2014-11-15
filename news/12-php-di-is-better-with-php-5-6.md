---
layout: blogpost
title: PHP 5.6 is out and PHP-DI just got better
author: Matthieu Napoli
date: September 27th 2014
---

[PHP 5.6 has been released](http://php.net/manual/en/migration56.new-features.php) a month ago and
PHP-DI just got better because of it!

As explained 9 months ago when I introduced the [new definition format for PHP-DI 4](06-php-di-4-0-new-definitions.md),
that format has been thought for the future.

It was imagined to take advantage of the short arrays introduced in PHP 5.4:

```php
return [
    'Acme\SomeModule\Service\Foo' => DI\object()
      ->constructor(DI\link('Acme\SomeModule\Service\Bar')),
];
```

Along with the `::class` of PHP 5.5:

```php
use Acme\SomeModule\Service\Foo;
use Acme\SomeModule\Service\Bar;

return [
    Foo::class => DI\object()
      ->constructor(DI\link(Bar::class)),
];
```

And now the `use function` of PHP 5.6:

```php
use Acme\SomeModule\Service\Foo;
use Acme\SomeModule\Service\Bar;
use function DI\object;
use function DI\link;

return [
    Foo::class => object()
      ->constructor(link(Bar::class))
];
```

Yay! Those helper functions can now be imported, which helps clearing up long configuration files:

- `DI\object()`
- `DI\link()`
- `DI\factory()`
- `DI\env()`

I have been using PHP 5.6 to build [isitmaintained.com](http://isitmaintained.com/) and I can
confirm, after a few week of usage, that this is a noticeable improvement.

If you want to see a real life example, have a look at the [configuration file](https://github.com/mnapoli/IsItMaintained/blob/master/app/config/config.php).

## What about you?

After 9 months of using the new definition format, what do you think of it?

Do you see any way to improve it? Or something to improve in the documentation?
