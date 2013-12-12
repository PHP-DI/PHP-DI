<?php

use DI\Scope;

return array(
    'foo' => 'bar',

    'IntegrationTests\DI\Fixtures\Class1' => DI\object()
            ->withScope(Scope::PROTOTYPE())
            ->withProperty('property1', DI\link('IntegrationTests\DI\Fixtures\Class2'))
            ->withProperty('property2', DI\link('IntegrationTests\DI\Fixtures\Interface1'))
            ->withProperty('property3', DI\link('namedDependency'))
            ->withProperty('property4', DI\link('foo'))
            ->withProperty('property5', DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
            ->withConstructor(
                DI\link('IntegrationTests\DI\Fixtures\Class2'),
                DI\link('IntegrationTests\DI\Fixtures\Interface1'),
                DI\link('IntegrationTests\DI\Fixtures\LazyDependency')
            )
            ->withMethod('method1', DI\link('IntegrationTests\DI\Fixtures\Class2'))
            ->withMethod('method2', DI\link('IntegrationTests\DI\Fixtures\Interface1'))
            ->withMethod('method3', DI\link('namedDependency'), DI\link('foo'))
            ->withMethod('method4', DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
            ->withMethodParameter('method5', 'param1', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
            ->withMethodParameter('method5', 'param2', \DI\link('foo')),

    'IntegrationTests\DI\Fixtures\Class2' => DI\object(),

    'IntegrationTests\DI\Fixtures\Implementation1' => DI\object(),

    'IntegrationTests\DI\Fixtures\Interface1' => DI\object('IntegrationTests\DI\Fixtures\Implementation1')
            ->withScope(Scope::SINGLETON()),

    'namedDependency' => DI\object('IntegrationTests\DI\Fixtures\Class2'),

    'IntegrationTests\DI\Fixtures\LazyDependency' => DI\object()
            ->lazy(),

    'alias' => DI\link('namedDependency'),
);
