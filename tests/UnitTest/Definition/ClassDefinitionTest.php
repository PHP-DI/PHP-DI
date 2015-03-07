<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ClassDefinition;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Scope;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\ClassDefinition
 */
class ClassDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getters_setters()
    {
        $definition = new ClassDefinition('foo', 'bar');
        $definition->setLazy(true);
        $definition->setScope(Scope::PROTOTYPE());

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function test_defaults()
    {
        $definition = new ClassDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertFalse($definition->isLazy());
        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
        $this->assertNull($definition->getConstructorInjection());
        $this->assertEmpty($definition->getPropertyInjections());
        $this->assertEmpty($definition->getMethodInjections());
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage DI definition conflict: there are 2 different definitions for 'foo' that are incompatible, they are not of the same type
     */
    public function should_only_merge_with_compatible_definitions()
    {
        $otherDefinition = EasyMock::mock('DI\Definition\MergeableDefinition');

        $definition = new ClassDefinition('foo', 'bar');
        $definition->merge($otherDefinition);
    }

    /**
     * @test
     * @dataProvider mergeDataProvider
     */
    public function should_merge_with_another_class_definition(
        ClassDefinition $definition1,
        ClassDefinition $definition2
    ) {
        $merged = $definition1->merge($definition2);

        // Check that the object is cloned
        $this->assertNotSame($definition1, $merged);

        $this->assertEquals('foo', $merged->getName());
        $this->assertEquals('bar', $merged->getClassName());
        $this->assertTrue($merged->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $merged->getScope());
        $this->assertNotNull($merged->getConstructorInjection());
        $this->assertCount(3, $merged->getPropertyInjections());
        $this->assertCount(3, $merged->getMethodInjections());
    }

    /**
     * @return array
     */
    public static function mergeDataProvider()
    {
        $definition1 = new ClassDefinition('foo', 'bar');
        $definition1->setLazy(true);
        $definition1->setScope(Scope::PROTOTYPE());
        $definition1->setConstructorInjection(MethodInjection::constructor());
        $definition1->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $definition1->addPropertyInjection(new PropertyInjection('property2', 'Property2'));
        $definition1->addMethodInjection(new MethodInjection('method1'));
        $definition1->addMethodInjection(new MethodInjection('method2'));

        $definition2 = new ClassDefinition('foo');
        $definition2->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $definition2->addPropertyInjection(new PropertyInjection('property3', 'Property3'));
        $definition2->addMethodInjection(new MethodInjection('method1'));
        $definition2->addMethodInjection(new MethodInjection('method3'));

        return array(
            array($definition1, $definition2),
            array($definition2, $definition1),
        );
    }

    public function should_be_cacheable()
    {
        $this->assertInstanceOf('DI\Definition\CacheableDefinition', new ClassDefinition('foo'));
    }
}
