<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;

/**
 * Test string definitions
 *
 * @coversNothing
 */
class StringDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_string_without_placeholder()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => \DI\string('bar'),
        ));
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo'));
    }

    public function test_string_with_placeholder()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo'         => 'bar',
            'test-string' => \DI\string('Hello {foo}'),
        ));
        $container = $builder->build();

        $this->assertEquals('Hello bar', $container->get('test-string'));
    }

    public function test_string_with_multiple_placeholders()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo'         => 'bar',
            'bim'         => 'bam',
            'test-string' => \DI\string('Hello {foo}, {bim}'),
        ));
        $container = $builder->build();

        $this->assertEquals('Hello bar, bam', $container->get('test-string'));
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while parsing string expression for entry 'test-string': No entry or class found for 'foo'
     */
    public function test_string_with_nonexistent_placeholder()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'test-string' => \DI\string('Hello {foo}'),
        ));
        $container = $builder->build();

        $container->get('test-string');
    }
}
