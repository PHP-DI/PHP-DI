<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\Source\AnnotationDefinitionSource;

/**
 * Test class for AnnotationDefinitionSource
 */
class AnnotationDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{

    public function testUnknownClass()
    {
        $source = new AnnotationDefinitionSource();
        $this->assertNull($source->getDefinition('foo'));
    }

    public function testProperty1()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $properties = $definition->getPropertyInjections();
        $this->assertInstanceOf('DI\Definition\PropertyInjection', $properties['property1']);

        $property = $properties['property1'];
        $this->assertEquals('property1', $property->getPropertyName());
        $this->assertEquals('foo', $property->getEntryName());
    }

    public function testConstructor()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
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
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method1'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertEmpty($parameterInjections);
    }

    public function testMethod2()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
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
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
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
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
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
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method5'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture2', $param1->getEntryName());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture2', $param2->getEntryName());
    }

    public function testMethod6()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
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

    public function testMethod7()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method7'];
        $this->assertInstanceOf('DI\Definition\MethodInjection', $methodInjection);

        $parameterInjections = $methodInjection->getParameterInjections();
        $this->assertCount(2, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('foo', $param1->getEntryName());
        $this->assertTrue($param1->isLazy());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertEquals('bar', $param2->getEntryName());
        $this->assertFalse($param2->isLazy());
    }

}
