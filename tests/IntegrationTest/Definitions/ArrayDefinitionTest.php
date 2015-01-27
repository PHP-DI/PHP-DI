<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Definitions;

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
        $builder->addDefinitions(__DIR__ . '/Fixtures/array-definitions.php');

        $this->container = $builder->build();
    }

    public function test_array_with_values()
    {
        $array = $this->container->get('values');

        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
    }

    public function test_array_with_links()
    {
        $array = $this->container->get('links');

        $this->assertTrue($array[0] instanceof \stdClass);
        $this->assertTrue($array[1] instanceof \stdClass);

        $singleton = $this->container->get('singleton');
        $prototype = $this->container->get('prototype');

        $this->assertSame($singleton, $array[0]);
        $this->assertNotSame($prototype, $array[1]);
    }

    public function test_add_entries()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Fixtures/array-definitions.php');
        $builder->addDefinitions(__DIR__ . '/Fixtures/array-extensions.php');
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(4, $array);
        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
        $this->assertEquals('another value', $array[2]);
        $this->assertTrue($array[3] instanceof \stdClass);
    }
}
