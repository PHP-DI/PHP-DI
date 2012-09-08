<?php

use \DI\Proxy\Proxy;
use \TestFixtures\Proxy\Class1;


/**
 * Proxy test class
 */
class ProxyTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var \TestFixtures\Proxy\Class1
	 */
	private $instance;
	/**
	 * @var \TestFixtures\Proxy\Class1
	 */
	private $proxy;

	public function setUp() {
		$instance = new Class1();
		$this->instance = $instance;
		$this->proxy = new Proxy(function() use($instance) {
			return $instance;
		});
	}

	public function testCall() {
		$this->assertTrue($this->proxy->getTrue());
	}

	/**
	 * @expectedException \DI\Proxy\ProxyException
	 */
	public function testCallStatic() {
		Proxy::getTrue();
	}

	public function testGet() {
		$this->assertTrue($this->proxy->property1);
	}

	public function testSet() {
		$this->proxy->property1 = false;
		$this->assertFalse($this->proxy->property1);
	}

	public function testIsset() {
		$this->assertTrue(isset($this->proxy->property1));
		$this->assertFalse(isset($this->proxy->unknownProperty));
	}

	public function testUnset() {
		unset($this->proxy->property1);
		$this->assertFalse(isset($this->proxy->property1));
	}

	public function testInvoke() {
		$proxy = $this->proxy;
		$this->assertTrue($proxy());
	}

	/**
	 * @expectedException \DI\Proxy\ProxyException
	 */
	public function testSetState() {
		Proxy::__set_state(array());
	}

	public function testClone() {
		// TODO
	}

	public function testToString() {
		$this->assertEquals("1", (string) $this->proxy);
	}

}
