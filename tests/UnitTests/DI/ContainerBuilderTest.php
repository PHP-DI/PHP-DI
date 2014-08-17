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
 *
 * @covers \DI\ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfiguration()
    {
        // Make the ContainerBuilder use our fake class to catch constructor parameters
        $builder = new ContainerBuilder('UnitTests\DI\Fixtures\FakeContainer');
        /** @var FakeContainer $container */
        $container = $builder->build();

        // No cache
        $this->assertNull($container->definitionManager->getCache());
    }

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
     */
    public function testContainerNotWrapped()
    {
        $builder = new ContainerBuilder('UnitTests\DI\Fixtures\FakeContainer');
        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertNull($container->wrapperContainer);
    }

    public function testContainerWrapped()
    {
        $otherContainer = $this->getMockForAbstractClass('Interop\Container\ContainerInterface');

        $builder = new ContainerBuilder('UnitTests\DI\Fixtures\FakeContainer');
        $builder->wrapContainer($otherContainer);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertSame($otherContainer, $container->wrapperContainer);
    }

    public function testFluentInterface()
    {
        $builder = new ContainerBuilder();

        $result = $builder->useAnnotations(false);
        $this->assertSame($builder, $result);

        $result = $builder->useAnnotations(true);
        $this->assertSame($builder, $result);

        $result = $builder->useAutowiring(false);
        $this->assertSame($builder, $result);

        $result = $builder->useAutowiring(true);
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(true, 'somedir');
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(false);
        $this->assertSame($builder, $result);

        $mockCache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $result = $builder->setDefinitionCache($mockCache);
        $this->assertSame($builder, $result);

        $result = $builder->wrapContainer($this->getMockForAbstractClass('Interop\Container\ContainerInterface'));
        $this->assertSame($builder, $result);
    }
}
