<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\Source\CachedDefinitionSource;

/**
 * Test class for CachedDefinitionSource
 */
class CachedDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{

    public function testGetters()
    {
        $otherSource = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cachedDefinitionSource = new CachedDefinitionSource($otherSource, $cache, true);
        $this->assertSame($otherSource, $cachedDefinitionSource->getDefinitionSource());
        $this->assertSame($cache, $cachedDefinitionSource->getCache());
        $this->assertTrue($cachedDefinitionSource->getDebug());
    }

    public function testGetClassDefinitionNotCached()
    {
        // Source
        $otherSource = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $otherSource->expects($this->any())->method('getDefinition')->will($this->returnValue('test'));
        // Cache
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->any())->method('fetch')->will($this->returnValue(false));
        // We expect that the cached source will save data in the cache
        $cache->expects($this->atLeastOnce())->method('save');
        // Test
        $cachedDefinitionSource = new CachedDefinitionSource($otherSource, $cache, false);
        $this->assertEquals('test', $cachedDefinitionSource->getDefinition('MyClass'));
    }

    public function testGetClassDefinitionCached()
    {
        // Source
        $otherSource = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $otherSource->expects($this->any())->method('getClassDefinition')->will($this->returnValue('test'));
        // Cache
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->any())->method('fetch')->will($this->returnValue('foo'));
        // We expect that the cached source will save data in the cache
        $cache->expects($this->never())->method('save');
        // Test
        $cachedDefinitionSource = new CachedDefinitionSource($otherSource, $cache, false);
        $this->assertEquals('foo', $cachedDefinitionSource->getDefinition('MyClass'));
    }

}
