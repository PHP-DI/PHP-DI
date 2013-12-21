<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\DefinitionManager;
use DI\Definition\ValueDefinition;

/**
 * Test class for DefinitionManager
 *
 * @covers \DI\Definition\DefinitionManager
 */
class DefinitionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DI\Definition\DefinitionManager
     */
    public function shouldGetFromCache()
    {
        $definitionManager = new DefinitionManager();

        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue('foo'));

        $definitionManager->setCache($cache);

        $this->assertEquals($cache, $definitionManager->getCache());

        $this->assertEquals('foo', $definitionManager->getDefinition('foo'));
    }
    /**
     * @test
     * @covers \DI\Definition\DefinitionManager
     */
    public function shouldSaveToCache()
    {
        $definitionManager = new DefinitionManager();

        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(false));
        $cache->expects($this->once())
            ->method('save');

        $definitionManager->setCache($cache);

        $this->assertNull($definitionManager->getDefinition('foo'));
    }

    /**
     * Tests that the given definition source is chained to the ArraySource and used.
     */
    public function testDefinitionSource()
    {
        $definition = $this->getMockForAbstractClass('DI\Definition\Definition');
        $definition->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));

        $source = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $source->expects($this->once())
            ->method('getDefinition')
            ->with('foo')
            ->will($this->returnValue($definition));

        $definitionManager = new DefinitionManager($source);

        $this->assertSame($definition, $definitionManager->getDefinition('foo'));
    }

    public function testAddDefinition()
    {
        $definitionManager = new DefinitionManager();
        $valueDefinition = new ValueDefinition('foo', 'bar');

        $definitionManager->addDefinition($valueDefinition);

        $this->assertSame($valueDefinition, $definitionManager->getDefinition('foo'));
    }
}
