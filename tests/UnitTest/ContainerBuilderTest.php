<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\ValueDefinition;
use DI\Test\UnitTest\Fixtures\FakeContainer;

/**
 * @covers \DI\ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfiguration()
    {
        // Make the ContainerBuilder use our fake class to catch constructor parameters
        $builder = new ContainerBuilder('DI\Test\UnitTest\Fixtures\FakeContainer');
        /** @var FakeContainer $container */
        $container = $builder->build();

        // No cache
        $this->assertNull($container->definitionManager->getCache());
    }

    public function testSetCache()
    {
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');

        $builder = new ContainerBuilder('DI\Test\UnitTest\Fixtures\FakeContainer');
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
        $builder = new ContainerBuilder('DI\Test\UnitTest\Fixtures\FakeContainer');
        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertNull($container->wrapperContainer);
    }

    public function testContainerWrapped()
    {
        $otherContainer = $this->getMockForAbstractClass('Interop\Container\ContainerInterface');

        $builder = new ContainerBuilder('DI\Test\UnitTest\Fixtures\FakeContainer');
        $builder->wrapContainer($otherContainer);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertSame($otherContainer, $container->wrapperContainer);
    }

    public function testAddCustomDefinitionSource()
    {
        $builder = new ContainerBuilder('DI\Test\UnitTest\Fixtures\FakeContainer');

        // Custom definition sources should be chained correctly
        $builder->addDefinitions(new ArrayDefinitionSource(array('foo' => 'bar')));
        $builder->addDefinitions(new ArrayDefinitionSource(array('foofoo' => 'barbar')));

        /** @var FakeContainer $container */
        $container = $builder->build();

        // We should be able to get entries from our custom definition sources
        /** @var ValueDefinition $definition */
        $definition = $container->definitionManager->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertSame('bar', $definition->getValue());
        $definition = $container->definitionManager->getDefinition('foofoo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertSame('barbar', $definition->getValue());
    }

    public function testAddDefinitionArray()
    {
        $builder = new ContainerBuilder('DI\Test\UnitTest\Fixtures\FakeContainer');

        // Custom definition sources should be chained correctly
        $builder->addDefinitions(array('foo' => 'bar'));
        $builder->addDefinitions(array('foofoo' => 'barbar'));

        /** @var FakeContainer $container */
        $container = $builder->build();

        /** @var ValueDefinition $definition */
        $definition = $container->definitionManager->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertSame('bar', $definition->getValue());
        $definition = $container->definitionManager->getDefinition('foofoo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertSame('barbar', $definition->getValue());
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
