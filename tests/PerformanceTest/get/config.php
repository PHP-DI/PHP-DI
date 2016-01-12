<?php

use Interop\Container\ContainerInterface;

return [
    'object'  => \DI\object('DI\Test\PerformanceTest\Get\GetFixture'),
    'value'   => 'foo',
    'string'  => \DI\string('Hello this is {value}'),
    'alias'   => \DI\get('string'),
    'factory' => \DI\factory(function (ContainerInterface $c) {
        return $c->get('object');
    }),
    'array' => [
        'foo',
        'bar',
        \DI\get('value'),
    ],
];
