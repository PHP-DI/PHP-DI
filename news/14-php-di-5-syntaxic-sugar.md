---
layout: blogpost
title: Syntactic sugar in PHP-DI 5
author: Matthieu Napoli
date: February 4th 2015
---

It's been quite some time since there was a bit of news here. The reason is that a lot of work has been done for PHP-DI 5! It is not yet ready, but it is coming along great!

I am not going to list everything that will be included in this next major version today. For now I'll simply give you a glimpse of the syntactic sugar that it will bring.

Here is an example of a config file using **PHP-DI 4** and PHP 5.6. I show `use function` statements explicitly to minimize confusion for those unfamiliar with PHP 5.6.

```php
<?php

use ...;
use function DI\factory;
use function DI\link;
use function DI\object;

return [
    'path.root' => __DIR__ . '/..',

    'path.cache' => factory(function (ContainerInterface $c) {
        return $c->get('path.root') . '/var/cache';
    }),

    PdfGenerator::class => factory(function () {
        return ...;
    }),

    'notification.handlers' => factory(function (ContainerInterface $c) {
        return [
            $c->get(EmailHandler::class),
            $c->get(TextHandler::class),
            $c->get(PushHandler::class),
        ];
    }),

    Cache::class => object(JsonFileCache::class)
        ->constructor(link('cache.filename')),
    'cache.filename' => factory(function (ContainerInterface $c) {
        return $c->get('path.cache') . '/cache.json';
    }),

];
```

And here is the same config with the future **PHP-DI 5**:

```php
<?php

use ...;
use function ...;

return [
    'path.root' => __DIR__ . '/..',

    // new DI\string() helper to write string expressions
    'path.cache' => string('{path.root}/var/cache'),

    // DI\factory is now optional when using closures
    PdfGenerator::class => function () {
        return ...;
    },

    // array of services, yay!
    'notification.handlers' => [
        link(EmailHandler::class),
        link(TextHandler::class),
        link(PushHandler::class),
    ],

    // inline definitions in other definitions!
    Cache::class => object(JsonFileCache::class)
        ->constructor(string('{path.cache}/cache.json')),

];
```

I will not get in more details today, things can always change before the release. Be also aware that **syntactic sugar is absolutely not the major feature for PHP-DI 5** :) But more on that in a future article!

If you have any comment or question, please use the comment box below or pop up on [GitHub](https://github.com/PHP-DI/PHP-DI) or in the [Gitter chat](https://gitter.im/PHP-DI/PHP-DI).
