<?php

use DI\Entry;
use DI\Scope;
use IntegrationTests\DI\Fixtures\Class1;
use IntegrationTests\DI\Fixtures\Class2;
use IntegrationTests\DI\Fixtures\Implementation1;
use IntegrationTests\DI\Fixtures\Interface1;
use IntegrationTests\DI\Fixtures\LazyDependency;

return [
    'foo' => 'bar',

    Class1::class          => Entry::object()
            ->withScope(Scope::PROTOTYPE())
            ->withProperty('property1', Entry::link(Class2::class))
            ->withProperty('property2', Entry::link(Interface1::class))
            ->withProperty('property3', Entry::link('namedDependency'))
            ->withProperty('property4', Entry::link('foo'))
            ->withProperty('property5', Entry::link(LazyDependency::class))
            ->withConstructor(
                Entry::link(Class2::class),
                Entry::link(Interface1::class),
                Entry::link(LazyDependency::class)
            )
            ->withMethod('method1', Entry::link(Class2::class))
            ->withMethod('method2', Entry::link(Interface1::class))
            ->withMethod('method3', Entry::link('namedDependency'), Entry::link('foo'))
            ->withMethod('method4', Entry::link(LazyDependency::class)),

    Class2::class          => Entry::object(),

    Implementation1::class => Entry::object(),

    Interface1::class      => Entry::object(Implementation1::class)
            ->withScope(Scope::SINGLETON()),

    'namedDependency'      => Entry::object(Class2::class),

    LazyDependency::class => Entry::object()
            ->lazy(),

    'alias'               => Entry::link('namedDependency'),
];
