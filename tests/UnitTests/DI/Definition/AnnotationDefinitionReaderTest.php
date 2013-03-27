<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\AnnotationDefinitionReader;

/**
 * Test class for AnnotationDefinitionReader
 */
class AnnotationDefinitionReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testUnknownClass()
    {
        $reader = new AnnotationDefinitionReader();
        $this->assertNull($reader->getDefinition('foo'));
    }

    public function testProperty1()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $properties = $definition->getPropertyInjections();
        $this->assertInstanceOf('DI\Definition\PropertyInjection', $properties['property1']);

        $property = $properties['property1'];
        $this->assertEquals('property1', $property->getPropertyName());
        $this->assertEquals('foo', $property->getEntryName());
    }

    public function testConstructor()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\MethodInjection', $constructorInjection);

        $parameterInjections = $constructorInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('foo', $param1->getEntryName());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('bar', $param2->getEntryName());
    }

    public function testMethod1()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method1'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertEmpty($parameterInjections);
    }

    public function testMethod2()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method2'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('foo', $param1->getEntryName());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('bar', $param2->getEntryName());
    }

    public function testMethod3()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method3'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('foo', $param1->getEntryName());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('bar', $param2->getEntryName());
    }

    public function testMethod4()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method4'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('bar', $param2->getEntryName());
    }

    public function testMethod5()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method5'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('UnitTests\DI\Definition\Fixtures\AnnotationFixture2', $param1->getEntryName());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('UnitTests\DI\Definition\Fixtures\AnnotationFixture2', $param2->getEntryName());
    }

    public function testMethod6()
    {
        $reader = new AnnotationDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method6'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('foo', $param1->getEntryName());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('bar', $param2->getEntryName());
    }

}
