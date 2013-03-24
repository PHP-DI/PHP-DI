<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\DefinitionReader;

use DI\Definition\CachedDefinitionReader;

/**
 * Test class for CachedDefinitionReader
 */
class CachedDefinitionReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testGetters()
    {
        $otherReader = $this->getMockForAbstractClass('DI\\Definition\\DefinitionReader');
        $cache = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\Cache');
        $cachedDefinitionReader = new CachedDefinitionReader($otherReader, $cache, true);
        $this->assertSame($otherReader, $cachedDefinitionReader->getDefinitionReader());
        $this->assertSame($cache, $cachedDefinitionReader->getCache());
        $this->assertTrue($cachedDefinitionReader->getDebug());
    }

    public function testGetClassDefinitionNotCached()
    {
        // Reader
        $otherReader = $this->getMockForAbstractClass('DI\\Definition\\DefinitionReader');
        $otherReader->expects($this->any())->method('getDefinition')->will($this->returnValue('test'));
        // Cache
        $cache = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\Cache');
        $cache->expects($this->any())->method('fetch')->will($this->returnValue(false));
        // We expect that the cached reader will save data in the cache
        $cache->expects($this->atLeastOnce())->method('save');
        // Test
        $cachedDefinitionReader = new CachedDefinitionReader($otherReader, $cache, false);
        $this->assertEquals('test', $cachedDefinitionReader->getDefinition('MyClass'));
    }

    public function testGetClassDefinitionCached()
    {
        // Reader
        $otherReader = $this->getMockForAbstractClass('DI\\Definition\\DefinitionReader');
        $otherReader->expects($this->any())->method('getClassDefinition')->will($this->returnValue('test'));
        // Cache
        $cache = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\Cache');
        $cache->expects($this->any())->method('fetch')->will($this->returnValue('foo'));
        // We expect that the cached reader will save data in the cache
        $cache->expects($this->never())->method('save');
        // Test
        $cachedDefinitionReader = new CachedDefinitionReader($otherReader, $cache, false);
        $this->assertEquals('foo', $cachedDefinitionReader->getDefinition('MyClass'));
    }

}
