<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\Annotation\Inject;
use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\Interface1;

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

        $object = $container->get(Interface1::class);
        $this->assertInstanceOf(Implementation1::class, $object);
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
