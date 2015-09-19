<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\Annotation\Inject;
use DI\ContainerBuilder;

/**
 * Test definitions using wildcards.
 *
 * @coversNothing
 */
class WildcardDefinitionsTest extends \PHPUnit_Framework_TestCase
{
    public function test_wildcards()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo*'                                 => 'bar',
            'DI\Test\IntegrationTest\*\Interface*' => \DI\object('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo1'));

        $object = $container->get('DI\Test\IntegrationTest\Fixtures\Interface1');
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\Implementation1', $object);
    }

    public function test_wildcards_as_dependency()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'DI\Test\IntegrationTest\*\Interface*' => \DI\object('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        /** @var WildcardDefinitionsTestFixture $object */
        $object = $container->get(__NAMESPACE__ . '\WildcardDefinitionsTestFixture');
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\Implementation1', $object->dependency);
    }
}

class WildcardDefinitionsTestFixture
{
    /**
     * @Inject
     * @var \DI\Test\IntegrationTest\Fixtures\Interface1
     */
    public $dependency;
}
