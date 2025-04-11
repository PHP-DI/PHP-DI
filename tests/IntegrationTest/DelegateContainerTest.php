<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\ContainerBuilder;
use function DI\get;
use function DI\string;

/**
 * Test that we can use a delegate container.
 */
class DelegateContainerTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_alias_to_dependency_in_delegate_container(ContainerBuilder $subContainerBuilder)
    {
        $rootContainer = new Container;
        $value = new \stdClass();
        $rootContainer->set('bar', $value);

        $subContainerBuilder->wrapContainer($rootContainer);
        $subContainerBuilder->addDefinitions([
            'foo' => get('bar'),
        ]);
        $subContainer = $subContainerBuilder->build();

        self::assertSame($value, $subContainer->get('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_string_expression_using_dependency_in_delegate_container(ContainerBuilder $subContainerBuilder)
    {
        $rootContainer = new Container([
            'bar' => 'hello',
        ]);

        $subContainerBuilder->wrapContainer($rootContainer);
        $subContainerBuilder->addDefinitions([
            'foo' => string('{bar} world'),
        ]);
        $subContainer = $subContainerBuilder->build();

        self::assertEquals('hello world', $subContainer->get('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_with_container_call(ContainerBuilder $subContainerBuilder)
    {
        $value = new \stdClass();
        $rootContainer = new Container([
            'stdClass' => $value,
        ]);

        $subContainerBuilder->wrapContainer($rootContainer);
        $subContainer = $subContainerBuilder->build();

        $result = $subContainer->call(function (\stdClass $foo) {
            return $foo;
        });
        self::assertSame($value, $result, 'The root container was not used for the type-hint');
    }
}
