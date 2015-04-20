<?php

use DI\Scope;

return array(
    'foo' => 'bar',

    'DI\Test\IntegrationTest\Fixtures\Class1' => DI\object()
            ->scope(Scope::PROTOTYPE)
            ->property('property1', DI\get('DI\Test\IntegrationTest\Fixtures\Class2'))
            ->property('property2', DI\get('DI\Test\IntegrationTest\Fixtures\Interface1'))
            ->property('property3', DI\get('namedDependency'))
            ->property('property4', DI\get('foo'))
            ->property('property5', DI\get('DI\Test\IntegrationTest\Fixtures\LazyDependency'))
            ->constructor(
                DI\get('DI\Test\IntegrationTest\Fixtures\Class2'),
                DI\get('DI\Test\IntegrationTest\Fixtures\Interface1'),
                DI\get('DI\Test\IntegrationTest\Fixtures\LazyDependency')
            )
            ->method('method1', DI\get('DI\Test\IntegrationTest\Fixtures\Class2'))
            ->method('method2', DI\get('DI\Test\IntegrationTest\Fixtures\Interface1'))
            ->method('method3', DI\get('namedDependency'), DI\get('foo'))
            ->method('method4', DI\get('DI\Test\IntegrationTest\Fixtures\LazyDependency'))
            ->methodParameter('method5', 'param1', \DI\get('DI\Test\IntegrationTest\Fixtures\Interface1'))
            ->methodParameter('method5', 'param2', \DI\get('foo')),

    'DI\Test\IntegrationTest\Fixtures\Class2' => DI\object(),

    'DI\Test\IntegrationTest\Fixtures\Implementation1' => DI\object(),

    'DI\Test\IntegrationTest\Fixtures\Interface1' => DI\object('DI\Test\IntegrationTest\Fixtures\Implementation1')
            ->scope(Scope::SINGLETON),
    'DI\Test\IntegrationTest\Fixtures\Interface2' => DI\object('DI\Test\IntegrationTest\Fixtures\Class3'),

    'namedDependency' => DI\object('DI\Test\IntegrationTest\Fixtures\Class2'),

    'DI\Test\IntegrationTest\Fixtures\LazyDependency' => DI\object()
            ->lazy(),

    'alias' => DI\get('namedDependency'),
);
