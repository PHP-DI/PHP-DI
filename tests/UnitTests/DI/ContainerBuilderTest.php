<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\ContainerBuilder;
use UnitTests\DI\Fixtures\FakeContainer;

/**
 * Test class for ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \DI\ContainerBuilder
     */
    public function testDefaultConfiguration()
    {
        // Make the ContainerBuilder use our fake class to catch constructor parameters
        $builder = new ContainerBuilder('UnitTests\DI\Fixtures\FakeContainer');
        /** @var FakeContainer $container */
        $container = $builder->build();

        // No cache
        $this->assertNull($container->definitionManager->getCache());
    }

    /**
     * @covers \DI\ContainerBuilder
     */
    public function testSetCache()
    {
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');

        $builder = new ContainerBuilder('UnitTests\DI\Fixtures\FakeContainer');
        $builder->setDefinitionCache($cache);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertSame($cache, $container->definitionManager->getCache());
    }

    /**
     * By default, the definition resolvers should not be overridden
     * @covers \DI\ContainerBuilder
     */
    public function testContainerNotWrapped()
    {
        $builder = new ContainerBuilder('UnitTests\DI\Fixtures\FakeContainer');
        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertNull($container->wrapperContainer);
    }

    /**
     * @covers \DI\ContainerBuilder
     */
    public function testContainerWrapped()
    {
        $otherContainer = $this->getMockForAbstractClass('DI\ContainerInterface');

        $builder = new ContainerBuilder('UnitTests\DI\Fixtures\FakeContainer');
        $builder->wrapContainer($otherContainer);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertSame($otherContainer, $container->wrapperContainer);
    }

    /**
     * @covers \DI\ContainerBuilder
     */
    public function testFluentInterface()
    {
        $builder = new ContainerBuilder();

        $result = $builder->useAnnotations(false);
        $this->assertSame($builder, $result);

        $result = $builder->useAnnotations(true);
        $this->assertSame($builder, $result);

        $result = $builder->useReflection(false);
        $this->assertSame($builder, $result);

        $result = $builder->useReflection(true);
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(true, 'somedir');
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(false);
        $this->assertSame($builder, $result);

        $mockCache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $result = $builder->setDefinitionCache($mockCache);
        $this->assertSame($builder, $result);

        $result = $builder->wrapContainer($this->getMockForAbstractClass('DI\ContainerInterface'));
        $this->assertSame($builder, $result);
    }
}
