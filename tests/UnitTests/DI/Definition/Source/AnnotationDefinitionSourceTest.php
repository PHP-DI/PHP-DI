<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\EntryReference;
use DI\Definition\MethodInjection;
use DI\Definition\PropertyInjection;
use DI\Definition\Source\AnnotationDefinitionSource;
use UnitTests\DI\Definition\Fixtures\AnnotationFixture;
use UnitTests\DI\Definition\Fixtures\AnnotationFixture2;

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
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $properties = $definition->getPropertyInjections();
        $this->assertInstanceOf(PropertyInjection::class, $properties['property1']);

        $property = $properties['property1'];
        $this->assertEquals('property1', $property->getPropertyName());
        $this->assertEquals(new EntryReference('foo'), $property->getValue());
    }

    public function testConstructor()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod1()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method1'];
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $this->assertEmpty($methodInjection->getParameters());
    }

    public function testMethod2()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method2'];
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod3()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method3'];
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod4()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method4'];
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod5()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method5'];
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);

        $this->assertEquals(new EntryReference(AnnotationFixture2::class), $parameters[0]);
        $this->assertEquals(new EntryReference(AnnotationFixture2::class), $parameters[1]);
    }

    public function testMethod6()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method6'];
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod7()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method7'];
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }
}
