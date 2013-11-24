<?php

use DI\Entry;
use DI\Scope;

return array(
    'foo' => 'bar',

    'IntegrationTests\DI\Fixtures\Class1' => Entry::object()
            ->withScope(Scope::PROTOTYPE())
            ->withProperty('property1', Entry::link('IntegrationTests\DI\Fixtures\Class2'))
            ->withProperty('property2', Entry::link('IntegrationTests\DI\Fixtures\Interface1'))
            ->withProperty('property3', Entry::link('namedDependency'))
            ->withProperty('property4', Entry::link('foo'))
            ->withProperty('property5', Entry::link('IntegrationTests\DI\Fixtures\LazyDependency'))
            ->withConstructor(
                Entry::link('IntegrationTests\DI\Fixtures\Class2'),
                Entry::link('IntegrationTests\DI\Fixtures\Interface1'),
                Entry::link('IntegrationTests\DI\Fixtures\LazyDependency')
            )
            ->withMethod('method1', Entry::link('IntegrationTests\DI\Fixtures\Class2'))
            ->withMethod('method2', Entry::link('IntegrationTests\DI\Fixtures\Interface1'))
            ->withMethod('method3', Entry::link('namedDependency'), Entry::link('foo'))
            ->withMethod('method4', Entry::link('IntegrationTests\DI\Fixtures\LazyDependency')),

    'IntegrationTests\DI\Fixtures\Class2' => Entry::object(),

    'IntegrationTests\DI\Fixtures\Implementation1' => Entry::object(),

    'IntegrationTests\DI\Fixtures\Interface1' => Entry::object('IntegrationTests\DI\Fixtures\Implementation1')
            ->withScope(Scope::SINGLETON()),

    'namedDependency' => Entry::object('IntegrationTests\DI\Fixtures\Class2'),

    'IntegrationTests\DI\Fixtures\LazyDependency' => Entry::object()
            ->lazy(),

    'alias' => Entry::link('namedDependency'),
);
