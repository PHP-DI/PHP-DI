<?php

use DI\Scope;
use DI\Test\IntegrationTest\Fixtures\Class1;
use DI\Test\IntegrationTest\Fixtures\Class2;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\Interface1;
use DI\Test\IntegrationTest\Fixtures\LazyDependency;

return [
    'foo' => 'bar',

    Class1::class => DI\create()
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
            ->method('method4', DI\get(LazyDependency::class)),

    Class2::class => DI\create(),

    Implementation1::class => DI\create(),

    Interface1::class => DI\create(Implementation1::class)
            ->scope(Scope::SINGLETON),
    'DI\Test\IntegrationTest\Fixtures\Interface2' => DI\create('DI\Test\IntegrationTest\Fixtures\Class3'),

    'namedDependency' => DI\create(Class2::class),

    LazyDependency::class => DI\create()->lazy(),
];
