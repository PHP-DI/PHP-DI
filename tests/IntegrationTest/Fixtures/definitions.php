<?php

use DI\Scope;
use DI\Test\IntegrationTest\Fixtures\Class1;
use DI\Test\IntegrationTest\Fixtures\Class2;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\Interface1;
use DI\Test\IntegrationTest\Fixtures\LazyDependency;

return [
    'foo' => 'bar',

    Class1::class => DI\object()
            ->scope(Scope::PROTOTYPE)
            ->property('property1', DI\get(Class2::class))
            ->property('property2', DI\get(Interface1::class))
            ->property('property3', DI\get('namedDependency'))
            ->property('property4', DI\get('foo'))
            ->property('property5', DI\get(LazyDependency::class))
            ->constructor(DI\get(Class2::class), DI\get(Interface1::class), DI\get(LazyDependency::class))
            ->method('method1', DI\get(Class2::class))
            ->method('method2', DI\get(Interface1::class))
            ->method('method3', DI\get('namedDependency'), DI\get('foo'))
            ->method('method4', DI\get(LazyDependency::class))
            ->methodParameter('method5', 'param1', \DI\get(Interface1::class))
            ->methodParameter('method5', 'param2', \DI\get('foo')),

    Class2::class => DI\object(),

    Implementation1::class => DI\object(),

    Interface1::class => DI\object(Implementation1::class)
            ->scope(Scope::SINGLETON),
    'DI\Test\IntegrationTest\Fixtures\Interface2' => DI\object('DI\Test\IntegrationTest\Fixtures\Class3'),

    'namedDependency' => DI\object(Class2::class),

    LazyDependency::class => DI\object()->lazy(),

    'alias' => DI\get('namedDependency'),
];
