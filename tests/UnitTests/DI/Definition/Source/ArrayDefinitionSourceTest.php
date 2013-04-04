<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\ClassDefinition;
use DI\Scope;

/**
 * Test class for ArrayDefinitionSource
 */
class ArrayDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{

    public function testValueDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => 'bar',
            )
        );
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('\\DI\\Definition\\ValueDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());
    }

    public function testValueTypes()
    {
        $source = new ArrayDefinitionSource();
        $definitions = array(
            'integer' => 1,
            'string'  => 'test',
            'float'   => 1.0,
        );
        $source->addDefinitions($definitions);

        $definition = $source->getDefinition('integer');
        $this->assertNotNull($definition);
        $this->assertEquals(1, $definition->getValue());
        $this->assertInternalType('integer', $definition->getValue());

        $definition = $source->getDefinition('string');
        $this->assertNotNull($definition);
        $this->assertEquals('test', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        $definition = $source->getDefinition('float');
        $this->assertNotNull($definition);
        $this->assertEquals(1.0, $definition->getValue());
        $this->assertInternalType('float', $definition->getValue());
    }

    public function testClassDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => array(
                    'lazy'  => true,
                    'scope' => 'prototype',
                ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testScopeDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'default' => array(
                ),
                'foo' => array(
                    'scope' => 'prototype',
                ),
                'bar' => array(
                    'scope' => Scope::PROTOTYPE,
                ),
                'bam' => array(
                    'scope' => Scope::PROTOTYPE(),
                ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('default');
        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
        $definition = $source->getDefinition('foo');
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
        $definition = $source->getDefinition('bar');
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
        $definition = $source->getDefinition('bam');
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testAliasDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => array(
                    'class' => 'Bar',
                ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('Bar', $definition->getClassName());
    }

    public function testPropertyDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => array(
                    'properties' => array(
                        'property1' => 'Property1',
                        'property2' => array(
                            'name' => 'Property2',
                            'lazy' => true,
                        ),
                    ),
                ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $propertyInjections = $definition->getPropertyInjections();
        $this->assertCount(2, $propertyInjections);

        $property1 = $propertyInjections['property1'];
        $this->assertEquals('property1', $property1->getPropertyName());
        $this->assertEquals('Property1', $property1->getEntryName());
        $this->assertFalse($property1->isLazy());

        $property2 = $propertyInjections['property2'];
        $this->assertEquals('property2', $property2->getPropertyName());
        $this->assertEquals('Property2', $property2->getEntryName());
        $this->assertTrue($property2->isLazy());
    }

    public function testMethodDefinition1()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => array(
                    'methods' => array(
                        'set1' => array(
                            'param1' => 'Foo1',
                            'param2' => array(
                                'name' => 'Foo2',
                            ),
                        ),
                    ),
                ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $methodInjections = $definition->getMethodInjections();
        $this->assertCount(1, $methodInjections);

        $method1 = $methodInjections['set1'];
        $this->assertEquals('set1', $method1->getMethodName());

        $parameters = $method1->getParameterInjections();

        $parameter1 = $parameters['param1'];
        $this->assertEquals('param1', $parameter1->getParameterName());
        $this->assertEquals('Foo1', $parameter1->getEntryName());

        $parameter2 = $parameters['param2'];
        $this->assertEquals('param2', $parameter2->getParameterName());
        $this->assertEquals('Foo2', $parameter2->getEntryName());
    }

    public function testMethodDefinition2()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                 'foo' => array(
                     'methods' => array(
                         'set1' => array('Foo1', 'Foo2'),
                     ),
                 ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $methodInjections = $definition->getMethodInjections();
        $this->assertCount(1, $methodInjections);

        $method1 = $methodInjections['set1'];
        $this->assertEquals('set1', $method1->getMethodName());

        $parameters = $method1->getParameterInjections();

        $parameter1 = $parameters[0];
        $this->assertEquals(0, $parameter1->getParameterName());
        $this->assertEquals('Foo1', $parameter1->getEntryName());

        $parameter2 = $parameters[1];
        $this->assertEquals(1, $parameter2->getParameterName());
        $this->assertEquals('Foo2', $parameter2->getEntryName());
    }

    public function testMethodDefinition3()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                 'foo' => array(
                     'methods' => array(
                         'set1' => 'Foo1',
                     ),
                 ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $methodInjections = $definition->getMethodInjections();
        $this->assertCount(1, $methodInjections);

        $method1 = $methodInjections['set1'];
        $this->assertEquals('set1', $method1->getMethodName());

        $parameters = $method1->getParameterInjections();

        $parameter1 = $parameters[0];
        $this->assertEquals(0, $parameter1->getParameterName());
        $this->assertEquals('Foo1', $parameter1->getEntryName());
    }

    public function testConstructorDefinition1()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => array(
                    'constructor' => array(
                        'param1' => 'Foo1',
                        'param2' => array(
                            'name' => 'Foo2',
                        ),
                    ),
                ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $constructorInjection = $definition->getConstructorInjection();

        $this->assertNotNull($constructorInjection);
        $this->assertEquals('__construct', $constructorInjection->getMethodName());

        $parameters = $constructorInjection->getParameterInjections();

        $parameter1 = $parameters['param1'];
        $this->assertEquals('param1', $parameter1->getParameterName());
        $this->assertEquals('Foo1', $parameter1->getEntryName());

        $parameter2 = $parameters['param2'];
        $this->assertEquals('param2', $parameter2->getParameterName());
        $this->assertEquals('Foo2', $parameter2->getEntryName());
    }

    /**
     * @expectedException \DI\Definition\DefinitionException
     * @expectedExceptionMessage Invalid key 'bar' in definition of entry 'foo'; Valid keys are: class, scope, lazy, constructor, properties, methods
     */
    public function testKeysValidation()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => array(
                    'bar' => true,
                ),
            )
        );
        $source->getDefinition('foo');
    }

    public function testClosureDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => function() {
                    return 'bar';
                },
            )
        );
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('\\DI\\Definition\\ClosureDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());

        $container = $this->getMockBuilder('DI\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals('bar', $definition->getValue($container));
    }

}
