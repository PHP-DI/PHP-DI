<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
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
        $otherReader = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cachedDefinitionReader = new CachedDefinitionSource($otherReader, $cache, true);
        $this->assertSame($otherReader, $cachedDefinitionReader->getDefinitionSource());
        $this->assertSame($cache, $cachedDefinitionReader->getCache());
        $this->assertTrue($cachedDefinitionReader->getDebug());
    }

    public function testGetClassDefinitionNotCached()
    {
        // Reader
        $otherReader = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $otherReader->expects($this->any())->method('getDefinition')->will($this->returnValue('test'));
        // Cache
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->any())->method('fetch')->will($this->returnValue(false));
        // We expect that the cached reader will save data in the cache
        $cache->expects($this->atLeastOnce())->method('save');
        // Test
        $cachedDefinitionReader = new CachedDefinitionSource($otherReader, $cache, false);
        $this->assertEquals('test', $cachedDefinitionReader->getDefinition('MyClass'));
    }

    public function testGetClassDefinitionCached()
    {
        // Reader
        $otherReader = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $otherReader->expects($this->any())->method('getClassDefinition')->will($this->returnValue('test'));
        // Cache
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->any())->method('fetch')->will($this->returnValue('foo'));
        // We expect that the cached reader will save data in the cache
        $cache->expects($this->never())->method('save');
        // Test
        $cachedDefinitionReader = new CachedDefinitionSource($otherReader, $cache, false);
        $this->assertEquals('foo', $cachedDefinitionReader->getDefinition('MyClass'));
    }

}
