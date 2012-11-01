<?php

namespace UnitTests\DI\MetadataReader;

use DI\MetadataReader\CachedMetadataReader;

/**
 * Test class for CachedMetadataReader
 */
class CachedMetadataReaderTest extends \PHPUnit_Framework_TestCase
{

	public function testGetters() {
		$otherReader = $this->getMockForAbstractClass('DI\\MetadataReader\\MetadataReader');
		$cache = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\Cache');
		$cachedMetadataReader = new CachedMetadataReader($otherReader, $cache, true);
		$this->assertSame($otherReader, $cachedMetadataReader->getMetadataReader());
		$this->assertSame($cache, $cachedMetadataReader->getCache());
		$this->assertTrue($cachedMetadataReader->getDebug());
	}

	public function testGetClassMetadataNotCached() {
		// Reader
		$otherReader = $this->getMockForAbstractClass('DI\\MetadataReader\\MetadataReader');
		$otherReader->expects($this->any())->method('getClassMetadata')->will($this->returnValue('test'));
		// Cache
		$cache = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\Cache');
		$cache->expects($this->any())->method('fetch')->will($this->returnValue(false));
		// We expect that the cached reader will save data in the cache
		$cache->expects($this->atLeastOnce())->method('save');
		// Test
		$cachedMetadataReader = new CachedMetadataReader($otherReader, $cache, false);
		$this->assertEquals('test', $cachedMetadataReader->getClassMetadata('MyClass'));
	}

	public function testGetClassMetadataCached() {
		// Reader
		$otherReader = $this->getMockForAbstractClass('DI\\MetadataReader\\MetadataReader');
		$otherReader->expects($this->any())->method('getClassMetadata')->will($this->returnValue('test'));
		// Cache
		$cache = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\Cache');
		$cache->expects($this->any())->method('fetch')->will($this->returnValue('foo'));
		// We expect that the cached reader will save data in the cache
		$cache->expects($this->never())->method('save');
		// Test
		$cachedMetadataReader = new CachedMetadataReader($otherReader, $cache, false);
		$this->assertEquals('foo', $cachedMetadataReader->getClassMetadata('MyClass'));
	}

}
