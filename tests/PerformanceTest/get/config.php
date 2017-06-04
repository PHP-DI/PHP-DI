<?php

declare(strict_types=1);
use DI\Test\PerformanceTest\Get\A;
use DI\Test\PerformanceTest\Get\B;
use Psr\Container\ContainerInterface;

return [
    'object'  => \DI\create('DI\Test\PerformanceTest\Get\GetFixture')
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

    A::class  => \DI\autowire()
        ->constructorParameter('value', \DI\get('string')),
    B::class  => \DI\autowire()
        ->method('setValue', \DI\string('Wow: {string}'), \DI\get('value')),
];
