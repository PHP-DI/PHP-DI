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
use Interop\Container\ContainerInterface;

/**
 * Test decorator definitions
 *
 * @coversNothing
 */
class DecoratorDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_decorate_value()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => 'bar',
        ));
        $builder->addDefinitions(array(
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'baz';
            }),
        ));
        $container = $builder->build();

        $this->assertEquals('barbaz', $container->get('foo'));
    }

    public function test_decorate_factory()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => function () {
                return 'bar';
            },
        ));
        $builder->addDefinitions(array(
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'baz';
            }),
        ));
        $container = $builder->build();

        $this->assertEquals('barbaz', $container->get('foo'));
    }

    public function test_decorate_object()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => \DI\object('stdClass'),
        ));
        $builder->addDefinitions(array(
            'foo' => \DI\decorate(function ($previous) {
                $previous->foo = 'bar';
                return $previous;
            }),
        ));
        $container = $builder->build();

        $object = $container->get('foo');
        $this->assertEquals('bar', $object->foo);
    }

    public function test_decorator_gets_container()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => 'hello ',
            'bar' => 'world',
        ));
        $builder->addDefinitions(array(
            'foo' => \DI\decorate(function ($previous, ContainerInterface $container) {
                return $previous . $container->get('bar');
            }),
        ));
        $container = $builder->build();

        $this->assertEquals('hello world', $container->get('foo'));
    }

    public function test_multiple_decorators()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => 'bar',
        ));
        $builder->addDefinitions(array(
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'baz';
            }),
        ));
        $builder->addDefinitions(array(
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'bam';
            }),
        ));
        $container = $builder->build();

        $this->assertEquals('barbazbam', $container->get('foo'));
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Entry "foo" decorates nothing: no previous definition with the same name was found
     */
    public function test_decorate_must_have_previous_definition()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => \DI\decorate(function ($previous) {
                return $previous;
            }),
        ));
        $container = $builder->build();
        $container->get('foo');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while resolving foo[0]. Decorators cannot be nested in another definition
     */
    public function test_decorator_in_array()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'foo' => array(
                \DI\decorate(function ($previous) {
                    return $previous;
                }),
            ),
        ));
        $container = $builder->build();
        $container->get('foo');
    }
}
