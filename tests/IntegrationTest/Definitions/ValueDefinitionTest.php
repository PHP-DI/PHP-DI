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

/**
 * Test value definitions
 *
 * @coversNothing
 */
class ValueDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Fixtures/value-definitions.php');

        $this->container = $builder->build();
    }

    public function test_value_definitions()
    {
        $this->assertEquals('foo', $this->container->get('string'));
        $this->assertEquals(123, $this->container->get('int'));
        $this->assertEquals(new \stdClass(), $this->container->get('object'));
        $this->assertEquals('foo', $this->container->get('helper'));
    }
}
