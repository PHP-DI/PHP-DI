---
layout: blogpost
title: PHP-DI 5.0 released
author: Matthieu Napoli
date: May 14th 2015
---

I am very excited to announce that, after 8 months of iterative work, PHP-DI 5.0 is released.

This is a new major version that comes with:

- minor backward compatibility breaks (nothing to get too alarmed about)
- API and syntax simplifications (aka syntactic sugar)
- new major features for modular systems (aka bundles, modules, plugins, …)
- performance improvements and a lighter package
- a new website, logo and half rewritten documentation

But the best news of all is that this new major version wasn't produced out of thin air: it has been used in [Piwik](https://piwik.org/) and developed as a rolling release (i.e. always stable) to test and evaluate new features hands on. Because of that, PHP-DI 5 has been in production since January 2015. **More that 100 000 Piwik installs in the wild are running PHP-DI 5 already!**

So let's start and have a look at what's new.

## BC breaks

PHP-DI 5 doesn't break major features so you will not have a lot of work to upgrade. Here are the main changes:

- PHP 5.3 is no longer supported (5.4 or above is required)
- annotations are now disabled by default (enabling them is one line of code)
- lazy injection is disabled by default for a much lighter package
- `DI\link()` is deprecated in favor of a new `DI\get()` (but still works, so not really a BC break)

The complete list of changes are detailed extensively in the [migration guide to 5.0](../doc/migration/5.0.md).

## API simplifications, aka syntactic sugar

PHP-DI wants to be the DI container *for humans*, simplicity of use is one of its most important feature. To improve the situation even more, a few improvements have been made.

Most of these changes have been explained in the previous blog article: [Syntactic sugar in PHP-DI 5](14-php-di-5-syntaxic-sugar.md). Here is a reminder of the most important improvements:

```php
<?php

use ...;
// PHP 5.6 example
use function ...;

return [
    'path.root' => __DIR__ . '/..',

    // new DI\string() helper to write string expressions
    'path.cache' => string('{path.root}/var/cache'),

    // DI\factory() is now optional when using closures
    PdfGenerator::class => function () {
        return ...;
    },

    // array of services, yay!
    'notification.handlers' => [
        get(EmailHandler::class),
        get(TextHandler::class),
        get(PushHandler::class),
    ],

    // nest definitions in other definitions!
    Cache::class => object(JsonFileCache::class)
        ->constructor(string('{path.cache}/cache.json')),
];
```
