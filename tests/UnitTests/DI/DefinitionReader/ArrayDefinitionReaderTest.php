<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\DefinitionReader;

use DI\Definition\ArrayDefinitionReader;
use DI\Definition\ClassDefinition;
use DI\Definition\PropertyInjection;
use DI\Scope;

/**
 * Test class for ArrayDefinitionReader
 */
class ArrayDefinitionReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testValueDefinition()
    {
        $reader = new ArrayDefinitionReader();
        $reader->addDefinitions(
            array(
                'foo' => 'bar',
            )
        );
        $definition = $reader->getDefinition('foo');
        $this->assertInstanceOf('\\DI\\Definition\\ValueDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());
    }

    public function testValueTypes()
    {
        $reader = new ArrayDefinitionReader();
        $definitions = array(
            'integer' => 1,
            'string'  => 'test',
            'float'   => 1.0,
        );
        $reader->addDefinitions($definitions);

        $definition = $reader->getDefinition('integer');
        $this->assertNotNull($definition);
        $this->assertEquals(1, $definition->getValue());
        $this->assertInternalType('integer', $definition->getValue());

        $definition = $reader->getDefinition('string');
        $this->assertNotNull($definition);
        $this->assertEquals('test', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        $definition = $reader->getDefinition('float');
        $this->assertNotNull($definition);
        $this->assertEquals(1.0, $definition->getValue());
        $this->assertInternalType('float', $definition->getValue());
    }

    public function testClassDefinition()
    {
        $reader = new ArrayDefinitionReader();
        $reader->addDefinitions(
            array(
                'foo' => array(
                    'lazy'  => true,
                    'scope' => 'prototype',
                ),
            )
        );
        /** @var $definition ClassDefinition */
        $definition = $reader->getDefinition('foo');
        $this->assertInstanceOf('\\DI\\Definition\\ClassDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testPropertyDefinition()
    {
        $reader = new ArrayDefinitionReader();
        $reader->addDefinitions(
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
        $definition = $reader->getDefinition('foo');
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

}
