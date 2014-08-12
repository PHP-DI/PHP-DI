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
 * Test environment variable definitions
 *
 * @coversNothing
 */
class EnvironmentVariableDefinitionTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');

        $this->container = $builder->build();
    }

    public function testEnvironmentVariable()
    {
        $expectedValue = getenv('USER');

        if (!$expectedValue) {
            $this->markTestSkipped(
                'This test relies on the presence of the USER environment variable.'
            );
        }

        $this->assertEquals($expectedValue, $this->container->get('defined-env'));
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The environment variable 'PHP_DI_DO_NOT_DEFINE_THIS' has not been defined
     */
    public function testUndefinedEnvironmentVariable()
    {
        $this->container->get('undefined-env');
    }

    public function testOptionalEnvironmentVariable()
    {
        $this->assertEquals('<default>', $this->container->get('optional-env'));
    }

    public function testOptionalEnvironmentVariableWithNullDefault()
    {
        $this->assertNull($this->container->get('optional-env-null'));
    }

    public function testOptionalEnvironmentVariableWithLinkedDefaultValue()
    {
        $this->assertEquals('bar', $this->container->get('optional-env-linked'));
    }
}
