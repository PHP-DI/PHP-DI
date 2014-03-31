<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\EntryReference;
use DI\Definition\Source\AnnotationDefinitionSource;
use DI\Scope;

/**
 * Test class for AnnotationDefinitionSource
 *
 * @covers \DI\Definition\Source\AnnotationDefinitionSource
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
        $this->assertInstanceOf('DI\Definition\ClassDefinition\PropertyInjection', $properties['property1']);

        $property = $properties['property1'];
        $this->assertEquals('property1', $property->getPropertyName());
        $this->assertEquals(new EntryReference('foo'), $property->getValue());
    }

    public function testUnannotatedProperty()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNull($definition->getPropertyInjection('unannotatedProperty'));
    }

    public function testStaticProperty()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNull($definition->getPropertyInjection('staticProperty'));
    }

    /**
     * @expectedException \DI\Definition\Exception\AnnotationException
     * @expectedExceptionMessage @Inject found on property UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture4::property but unable to guess what to inject, use a @var annotation
     */
    public function testUnguessableProperty()
    {
        $source = new AnnotationDefinitionSource();
        $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture4');
    }

    public function testConstructor()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod1()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method1'];
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $methodInjection);

        $this->assertEmpty($methodInjection->getParameters());
    }

    public function testMethod2()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method2'];
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod3()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method3'];
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);

        $reference = new EntryReference('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture2');
        $this->assertEquals($reference, $parameters[0]);
        $this->assertEquals($reference, $parameters[1]);
    }

    public function testMethod4()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method4'];
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod5()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method5'];
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        // Offset is 1, not 0, because parameter 0 wasn't defined
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testUnannotatedMethod()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNull($definition->getMethodInjection('unannotatedMethod'));
    }

    public function testStaticMethod()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNull($definition->getMethodInjection('staticMethod'));
    }

    public function testInjectable()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationInjectableFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
        $this->assertTrue($definition->isLazy());
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/99
     */
    public function testIssue99()
    {
        $source = new AnnotationDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture3');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjections = $definition->getMethodInjections();
        $methodInjection = $methodInjections['method1'];
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals(
            new EntryReference('UnitTests\DI\Definition\Source\Fixtures\AnnotationFixture2'),
            $parameters[0]
        );
    }

    public function testSetAnnotationReader()
    {
        $reader = $this->getMockForAbstractClass('Doctrine\Common\Annotations\Reader');

        $source = new AnnotationDefinitionSource();
        $source->setAnnotationReader($reader);

        $this->assertSame($reader, $source->getAnnotationReader());
    }
}
