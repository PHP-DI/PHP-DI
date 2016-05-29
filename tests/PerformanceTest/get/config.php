<?php

use Interop\Container\ContainerInterface;

return [
    'object'  => \DI\object('DI\Test\PerformanceTest\Get\GetFixture')
        ->constructor(\DI\get('array')),
    'value'   => 'foo',
    'string'  => \DI\string('Hello this is {value}'),
    'alias'   => \DI\get('factory'),
    'factory' => \DI\factory(function (ContainerInterface $c) {
        return $c->get('object');
    }),
    'array' => [
        'foo',
        'bar',
        \DI\get('string'),
    ],
];
