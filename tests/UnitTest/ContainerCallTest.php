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

/**
 * Tests the call() method from the container.
 *
 * @covers \DI\Container
 */
class ContainerCallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function call_should_invoke_closures()
    {
        $container = ContainerBuilder::buildDevContainer();

        $result = $container->call(function () {
            return 'foo';
        });

        $this->assertEquals('foo', $result);
    }

    /**
     * @test
     */
    public function call_should_invoke_array_callables_on_objects()
    {
        $container = ContainerBuilder::buildDevContainer();

        $result = $container->call([$this, 'method']);

        $this->assertEquals('foo', $result);
    }

    /**
     * @test
     */
    public function call_should_invoke_array_callables_on_classes()
    {
        $container = ContainerBuilder::buildDevContainer();

        $result = $container->call([get_class(), 'staticMethod']);

        $this->assertEquals('bar', $result);
    }

    /**
     * @test
     */
    public function call_should_pass_parameters()
    {
        $container = ContainerBuilder::buildDevContainer();

        $result = $container->call(function ($param1, $param2) {
            return $param1 . $param2;
        }, [
            'param1' => 'foo',
            'param2' => 'bar',
        ]);

        $this->assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function parameters_should_be_indexed_by_name()
    {
        $container = ContainerBuilder::buildDevContainer();

        $result = $container->call(function ($param1, $param2) {
            return $param1 . $param2;
        }, [
            // Reverse order: should still work
            'param2' => 'bar',
            'param1' => 'foo',
        ]);

        $this->assertEquals('foobar', $result);
    }

    public function method()
    {
        return 'foo';
    }

    public static function staticMethod()
    {
        return 'bar';
    }
}
