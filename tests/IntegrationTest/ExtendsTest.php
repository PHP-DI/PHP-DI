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
 * Test definitions extending each others.
 *
 * @coversNothing
 */
class ExtendsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');
        $builder->addDefinitions(__DIR__ . '/Fixtures/extensions.php');

        $this->container = $builder->build();
    }

    public function test_extend_definition()
    {
        $array = $this->container->get('array');

        $this->assertCount(5, $array);
        $this->assertEquals('value', $array[0]);
        $this->assertTrue($array[1] instanceof Class1);
        $this->assertTrue($array[2] instanceof Class2);
        $this->assertEquals('another value', $array[3]);
        $this->assertTrue($array[4] instanceof Interface1);
    }

    public function test_extend_definition_with_another_name()
    {
        $array = $this->container->get('extend-array');

        $this->assertCount(2, $array);
        $this->assertEquals('value', $array[0]);
        $this->assertEquals('a second value', $array[1]);
    }

    public function test_extend_definition_same_file_with_another_name()
    {
        $array = $this->container->get('extend-array-same-file');

        $this->assertCount(6, $array);
        $this->assertEquals('value', $array[0]);
        $this->assertTrue($array[1] instanceof Class1);
        $this->assertTrue($array[2] instanceof Class2);
        $this->assertEquals('another value', $array[3]);
        $this->assertTrue($array[4] instanceof Interface1);
        $this->assertEquals('a third value', $array[5]);
    }
}
