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

/**
 * Test string definitions
 *
 * @coversNothing
 */
class StringDefinitionTest extends \PHPUnit_Framework_TestCase
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

    public function test_string_definition()
    {
        $this->assertEquals('Hello bar', $this->container->get('test-string'));
    }
}
