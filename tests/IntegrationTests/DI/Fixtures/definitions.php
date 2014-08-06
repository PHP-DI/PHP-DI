<?php

use DI\Scope;

return array(
    'foo' => 'bar',

    'IntegrationTests\DI\Fixtures\Class1' => DI\object()
            ->scope(Scope::PROTOTYPE())
            ->property('property1', DI\link('IntegrationTests\DI\Fixtures\Class2'))
            ->property('property2', DI\link('IntegrationTests\DI\Fixtures\Interface1'))
            ->property('property3', DI\link('namedDependency'))
            ->property('property4', DI\link('foo'))
            ->property('property5', DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
            ->constructor(
                DI\link('IntegrationTests\DI\Fixtures\Class2'),
                DI\link('IntegrationTests\DI\Fixtures\Interface1'),
                DI\link('IntegrationTests\DI\Fixtures\LazyDependency')
            )
            ->method('method1', DI\link('IntegrationTests\DI\Fixtures\Class2'))
            ->method('method2', DI\link('IntegrationTests\DI\Fixtures\Interface1'))
            ->method('method3', DI\link('namedDependency'), DI\link('foo'))
            ->method('method4', DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
            ->methodParameter('method5', 'param1', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
            ->methodParameter('method5', 'param2', \DI\link('foo')),

    'IntegrationTests\DI\Fixtures\Class2' => DI\object(),

    'IntegrationTests\DI\Fixtures\Implementation1' => DI\object(),

    'IntegrationTests\DI\Fixtures\Interface1' => DI\object('IntegrationTests\DI\Fixtures\Implementation1')
            ->scope(Scope::SINGLETON()),
    'IntegrationTests\DI\Fixtures\Interface2' => DI\object('IntegrationTests\DI\Fixtures\Class3'),

    'namedDependency' => DI\object('IntegrationTests\DI\Fixtures\Class2'),

    'IntegrationTests\DI\Fixtures\LazyDependency' => DI\object()
            ->lazy(),

    'alias' => DI\link('namedDependency'),

    'defined-env' => DI\env('USER'),
    'undefined-env' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS'),
    'optional-env' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', '<default>'),
    'optional-env-linked' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', DI\link('foo')),
);
