<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\ClassDefinition;
use DI\Definition\DefinitionException;
use DI\Definition\MethodInjection;
use DI\Definition\PropertyInjection;
use DI\Definition\ValueDefinition;
use DI\Scope;

/**
 * Test class for ClassDefinition
 */
class ClassDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testProperties()
    {
        $definition = new ClassDefinition('foo', 'bar');
        $definition->setLazy(true);
        $definition->setScope(Scope::PROTOTYPE());

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testDefaultValues()
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
     * @expectedException \DI\Definition\DefinitionException
     * @expectedExceptionMessage DI definition conflict: there are 2 different definitions for 'foo' that are incompatible, they are not of the same type
     */
    public function testMergeIncompatibleTypes()
    {
        $definition = new ClassDefinition('foo', 'bar');
        $definition->merge(new ValueDefinition('foo', 1));
    }

    /**
     * @dataProvider mergeDataProvider
     */
    public function testMerge(ClassDefinition $definition1, ClassDefinition $definition2)
    {
        $definition1->merge($definition2);

        $this->assertEquals('foo', $definition1->getName());
        $this->assertEquals('bar', $definition1->getClassName());
        $this->assertTrue($definition1->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $definition1->getScope());
        $this->assertNotNull($definition1->getConstructorInjection());
        $this->assertCount(3, $definition1->getPropertyInjections());
        $this->assertCount(3, $definition1->getMethodInjections());
    }

    /**
     * @return array
     */
    public static function mergeDataProvider()
    {
        $definition1 = new ClassDefinition('foo', 'bar');
        $definition1->setLazy(true);
        $definition1->setScope(Scope::PROTOTYPE());
        $definition1->setConstructorInjection(new MethodInjection('__construct'));
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

}
