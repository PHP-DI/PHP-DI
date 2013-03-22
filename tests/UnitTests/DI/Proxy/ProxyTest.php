<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Proxy;

use \DI\Proxy\Proxy;
use \UnitTests\DI\Proxy\Fixtures\ProxyTest\Class1;

/**
 * Proxy test class
 */
class ProxyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Class1
     */
    private $instance;
    /**
     * @var Class1
     */
    private $proxy;

    public function setUp()
    {
        $instance = new Class1();
        $this->instance = $instance;
        $this->proxy = new Proxy(function () use ($instance) {
            return $instance;
        });
    }

    public function testCall()
    {
        $this->assertTrue($this->proxy->getTrue());
    }

    /**
     * @expectedException \DI\Proxy\ProxyException
     */
    public function testCallStatic()
    {
        Proxy::unknownMethod();
    }

    public function testGet()
    {
        $this->assertTrue($this->proxy->property1);
    }

    public function testSet()
    {
        $this->proxy->property1 = false;
        $this->assertFalse($this->proxy->property1);
    }

    public function testIsset()
    {
        $this->assertTrue(isset($this->proxy->property1));
        $this->assertFalse(isset($this->proxy->unknownProperty));
    }

    public function testUnset()
    {
        unset($this->proxy->property1);
        $this->assertFalse(isset($this->proxy->property1));
    }

    public function testInvoke()
    {
        $proxy = $this->proxy;
        $this->assertTrue($proxy());
    }

    /**
     * @expectedException \DI\Proxy\ProxyException
     */
    public function testSetState()
    {
        Proxy::__set_state(array());
    }

    public function testClone()
    {
        // TODO
    }

    public function testToString()
    {
        $this->assertEquals("1", (string) $this->proxy);
    }

}
