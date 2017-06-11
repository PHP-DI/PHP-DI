<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

return [

    'stdClass' => function () {
        return new stdClass;
    },

    'empty' => DI\factory(function () {
        return null;
    }),

    'container' => DI\factory(function (ContainerInterface $c) {
        return null;
    }),

    'entry' => DI\factory(function (ContainerInterface $c) {
        return $c->get('stdClass');
    }),

    Foo::class => function () {
        return new Foo;
    },
    Bar::class => function (Foo $foo) {
        return new Bar;
    },
    'injected-entry' => function (Bar $bar) {
        return new stdClass;
    },

];
