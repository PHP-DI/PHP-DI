<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\Annotation\Inject;
use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\Interface1;

/**
 * Test definitions using wildcards.
 */
class WildcardDefinitionsTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_wildcards(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo*' => 'bar',
            'DI\Test\IntegrationTest\*\Interface*' => \DI\create('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo1'));

        $object = $container->get(Interface1::class);
        $this->assertInstanceOf(Implementation1::class, $object);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_wildcards_as_dependency(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'DI\Test\IntegrationTest\*\Interface*' => \DI\create('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        /** @var WildcardDefinitionsTestFixture $object */
        $object = $container->get(WildcardDefinitionsTestFixture::class);
        $this->assertInstanceOf(Implementation1::class, $object->dependency);
    }
}

class WildcardDefinitionsTestFixture
{
    /**
     * @Inject
     * @var Interface1
     */
    public $dependency;
}
