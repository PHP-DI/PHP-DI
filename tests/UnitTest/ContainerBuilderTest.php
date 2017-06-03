<?php

namespace DI\Test\UnitTest;

use DI\CompiledContainer;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\ValueDefinition;
use DI\Test\UnitTest\Fixtures\FakeContainer;
use EasyMock\EasyMock;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_configure_for_development_by_default()
    {
        // Make the ContainerBuilder use our fake class to catch constructor parameters
        $builder = new ContainerBuilder(FakeContainer::class);
        /** @var FakeContainer $container */
        $container = $builder->build();

        // Not compiled
        $this->assertFalse($container instanceof CompiledContainer);
        // Proxies evaluated in memory
        $this->assertFalse($this->getObjectAttribute($container->proxyFactory, 'writeProxiesToFile'));
    }

    /**
     * @test
     */
    public function the_container_should_not_be_wrapped_by_default()
    {
        $builder = new ContainerBuilder(FakeContainer::class);
        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertNull($container->wrapperContainer);
    }

    /**
     * @test
     */
    public function should_allow_to_set_a_wrapper_container()
    {
        $otherContainer = $this->easyMock(ContainerInterface::class);

        $builder = new ContainerBuilder(FakeContainer::class);
        $builder->wrapContainer($otherContainer);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertSame($otherContainer, $container->wrapperContainer);
    }

    /**
     * @test
     */
    public function should_allow_to_add_custom_definition_sources()
    {
        $builder = new ContainerBuilder(FakeContainer::class);

        // Custom definition sources should be chained correctly
        $builder->addDefinitions(new DefinitionArray(['foo' => 'bar']));
        $builder->addDefinitions(new DefinitionArray(['foofoo' => 'barbar']));

        /** @var FakeContainer $container */
        $container = $builder->build();

        // We should be able to get entries from our custom definition sources
        /** @var ValueDefinition $definition */
        $definition = $container->definitionSource->getDefinition('foo');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertSame('bar', $definition->getValue());
        $definition = $container->definitionSource->getDefinition('foofoo');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertSame('barbar', $definition->getValue());
    }

    /**
     * @test
     */
    public function should_chain_definition_sources_in_reverse_order()
    {
        $builder = new ContainerBuilder(FakeContainer::class);

        $builder->addDefinitions(new DefinitionArray(['foo' => 'bar']));
        $builder->addDefinitions(new DefinitionArray(['foo' => 'bim']));

        /** @var FakeContainer $container */
        $container = $builder->build();

        /** @var ValueDefinition $definition */
        $definition = $container->definitionSource->getDefinition('foo');
        $this->assertSame('bim', $definition->getValue());
    }

    /**
     * @test
     */
    public function should_allow_to_add_definitions_in_an_array()
    {
        $builder = new ContainerBuilder(FakeContainer::class);

        // Custom definition sources should be chained correctly
        $builder->addDefinitions(['foo' => 'bar']);
        $builder->addDefinitions(['foofoo' => 'barbar']);

        /** @var FakeContainer $container */
        $container = $builder->build();

        /** @var ValueDefinition $definition */
        $definition = $container->definitionSource->getDefinition('foo');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertSame('bar', $definition->getValue());
        $definition = $container->definitionSource->getDefinition('foofoo');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertSame('barbar', $definition->getValue());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ContainerBuilder::addDefinitions() parameter must be a string, an array or a DefinitionSource object, integer given
     */
    public function errors_when_adding_invalid_definitions()
    {
        $builder = new ContainerBuilder(FakeContainer::class);
        $builder->addDefinitions(123);
    }

    /**
     * @test
     */
    public function should_allow_to_create_a_compiled_container()
    {
        $builder = new ContainerBuilder();
        $builder->compile(__DIR__ . '/../IntegrationTest/tmp/CompiledContainer.php');

        $this->assertInstanceOf(CompiledContainer::class, $builder->build());
    }

    /**
     * @test
     */
    public function should_have_a_fluent_interface()
    {
        $builder = new ContainerBuilder();

        $result = $builder->useAnnotations(false);
        $this->assertSame($builder, $result);

        $result = $builder->useAnnotations(true);
        $this->assertSame($builder, $result);

        $result = $builder->useAutowiring(false);
        $this->assertSame($builder, $result);

        $result = $builder->useAutowiring(true);
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(true, 'somedir');
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(false);
        $this->assertSame($builder, $result);

        $result = $builder->compile('foo.php');
        $this->assertSame($builder, $result);

        $result = $builder->wrapContainer($this->easyMock(ContainerInterface::class));
        $this->assertSame($builder, $result);
    }

    /**
     * Ensure the ContainerBuilder cannot be modified after the container has been built.
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage The ContainerBuilder cannot be modified after the container has been built
     */
    public function should_throw_if_modified_after_building_a_container()
    {
        $builder = new ContainerBuilder();
        $builder->build();

        $builder->addDefinitions([]);
    }

    /**
     * @test
     */
    public function dev_container_configuration_should_be_identical_to_creating_a_new_container_from_defaults()
    {
        self::assertEquals(new Container, ContainerBuilder::buildDevContainer());
    }
}
