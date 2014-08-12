<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;

/**
 * Call functions.
 *
 * @coversNothing
 */
class CallFunctionTest extends \PHPUnit_Framework_TestCase
{
    public function testNoParameters()
    {
        $container = ContainerBuilder::buildDevContainer();
        $result = $container->call(function() {
            return 42;
        });
        $this->assertEquals(42, $result);
    }

    public function testParametersIndexedByName()
    {
        $container = ContainerBuilder::buildDevContainer();
        $result = $container->call(function($foo) {
            return $foo;
        }, array(
            'foo' => 'bar',
        ));
        $this->assertEquals('bar', $result);
    }

    public function testParameterWithLink()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->set('bar', 'bam');

        $result = $container->call(function($foo) {
            return $foo;
        }, array(
            'foo' => \DI\link('bar'),
        ));
        $this->assertEquals('bam', $result);
    }

    public function testParameterFromContainer()
    {
        $container = ContainerBuilder::buildDevContainer();

        $value = new \stdClass();
        $container->set('stdClass', $value);

        $result = $container->call(function(\stdClass $foo) {
            return $foo;
        });
        $this->assertEquals($value, $result);
    }

    public function testCallObjectMethod()
    {
        $container = ContainerBuilder::buildDevContainer();
        $object = new TestClass();
        $result = $container->call(array($object, 'foo'));
        $this->assertEquals(42, $result);
    }

    public function testCallStringMethodNonStatic() 
    {
        $container = ContainerBuilder::buildDevContainer();
        $class = __NAMESPACE__ . '\TestClass';
        $result = $container->call(array($class, 'foo'));
        $this->assertEquals(42, $result);
    }

    public function testCallStringMethodStatic() 
    {
        $container = ContainerBuilder::buildDevContainer();
        $class = __NAMESPACE__ . '\TestClass';
        $result = $container->call(array($class, 'bar'));
        $this->assertEquals(24, $result);
    }
}

class TestClass
{
    public function foo()
    {
        return 42;
    }
    
    public static function bar()
    {
        return 24;
    }
}
