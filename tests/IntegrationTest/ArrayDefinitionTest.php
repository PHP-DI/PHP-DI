<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\Class1;
use DI\Test\IntegrationTest\Fixtures\Class2;
use DI\Test\IntegrationTest\Fixtures\Interface1;

/**
 * Test array definitions
 *
 * @coversNothing
 */
class ArrayDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');

        $this->container = $builder->build();
    }

    public function test_array_definitions()
    {
        $array = $this->container->get('array');

        $this->assertEquals('value', $array[0]);
        $this->assertTrue($array[1] instanceof Class1);
        $this->assertTrue($array[2] instanceof Class2);
    }

    public function test_link_in_array()
    {
        $array = $this->container->get('array');
        $class1 = $this->container->get('DI\Test\IntegrationTest\Fixtures\Class1');
        $class2 = $this->container->get('DI\Test\IntegrationTest\Fixtures\Class2');

        // Class1 is registered with the prototype scope so it shouldn't be the same instance
        $this->assertNotSame($class1, $array[1]);

        $this->assertSame($class2, $array[2]);
    }

    public function test_add_entries()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');
        $builder->addDefinitions(__DIR__ . '/Fixtures/extensions.php');
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertCount(5, $array);
        $this->assertEquals('value', $array[0]);
        $this->assertTrue($array[1] instanceof Class1);
        $this->assertTrue($array[2] instanceof Class2);
        $this->assertEquals('another value', $array[3]);
        $this->assertTrue($array[4] instanceof Interface1);
    }
}
