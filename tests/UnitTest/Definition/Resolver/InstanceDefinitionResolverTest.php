<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\ClassDefinition;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Definition\InstanceDefinition;
use DI\Definition\Resolver\InstanceDefinitionResolver;
use DI\Definition\Resolver\ResolverDispatcher;
use DI\Proxy\ProxyFactory;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\InstanceDefinitionResolver
 */
class InstanceDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_inject_properties_on_instance()
    {
        $instance = new FixtureClass('');

        $classDefinition = new ClassDefinition(get_class($instance));
        $classDefinition->addPropertyInjection(new PropertyInjection('prop', 'value'));

        $resolver = $this->buildResolver();
        $resolver->resolve(new InstanceDefinition($instance, $classDefinition));

        $this->assertEquals('value', $instance->prop);
    }

    /**
     * @test
     */
    public function it_should_inject_methods_on_instance()
    {
        $instance = new FixtureClass('');

        $classDefinition = new ClassDefinition(get_class($instance));
        $classDefinition->addMethodInjection(new MethodInjection('method', array('value')));

        $resolver = $this->buildResolver();
        $resolver->resolve(new InstanceDefinition($instance, $classDefinition));

        $this->assertEquals('value', $instance->methodParam1);
    }

    private function buildResolver()
    {
        /** @var ResolverDispatcher $resolverDispatcher */
        $resolverDispatcher = EasyMock::mock('DI\Definition\Resolver\ResolverDispatcher');
        /** @var ProxyFactory $factory */
        $factory = EasyMock::mock('DI\Proxy\ProxyFactory');

        return new InstanceDefinitionResolver($resolverDispatcher, $factory);
    }
}
