<?php

use DI\Scope;

return array(
    'foo' => 'bar',

    'DI\Test\IntegrationTest\Fixtures\Class1' => DI\object()
            ->scope(Scope::PROTOTYPE())
            ->property('property1', DI\link('DI\Test\IntegrationTest\Fixtures\Class2'))
            ->property('property2', DI\link('DI\Test\IntegrationTest\Fixtures\Interface1'))
            ->property('property3', DI\link('namedDependency'))
            ->property('property4', DI\link('foo'))
            ->property('property5', DI\link('DI\Test\IntegrationTest\Fixtures\LazyDependency'))
            ->constructor(
                DI\link('DI\Test\IntegrationTest\Fixtures\Class2'),
                DI\link('DI\Test\IntegrationTest\Fixtures\Interface1'),
                DI\link('DI\Test\IntegrationTest\Fixtures\LazyDependency')
            )
            ->method('method1', DI\link('DI\Test\IntegrationTest\Fixtures\Class2'))
            ->method('method2', DI\link('DI\Test\IntegrationTest\Fixtures\Interface1'))
            ->method('method3', DI\link('namedDependency'), DI\link('foo'))
            ->method('method4', DI\link('DI\Test\IntegrationTest\Fixtures\LazyDependency'))
            ->methodParameter('method5', 'param1', \DI\link('DI\Test\IntegrationTest\Fixtures\Interface1'))
            ->methodParameter('method5', 'param2', \DI\link('foo')),

    'DI\Test\IntegrationTest\Fixtures\Class2' => DI\object(),

    'DI\Test\IntegrationTest\Fixtures\Implementation1' => DI\object(),

    'DI\Test\IntegrationTest\Fixtures\Interface1' => DI\object('DI\Test\IntegrationTest\Fixtures\Implementation1')
            ->scope(Scope::SINGLETON()),
    'DI\Test\IntegrationTest\Fixtures\Interface2' => DI\object('DI\Test\IntegrationTest\Fixtures\Class3'),

    'namedDependency' => DI\object('DI\Test\IntegrationTest\Fixtures\Class2'),

    'DI\Test\IntegrationTest\Fixtures\LazyDependency' => DI\object()
            ->lazy(),

    'alias' => DI\link('namedDependency'),

    'defined-env' => DI\env('USER'),
    'undefined-env' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS'),
    'optional-env' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', '<default>'),
    'optional-env-null' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', null),
    'optional-env-linked' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', DI\link('foo')),

    'array' => array(
        'value',
        DI\link('DI\Test\IntegrationTest\Fixtures\Class1'),
        DI\link('DI\Test\IntegrationTest\Fixtures\Class2'),
    ),
);
