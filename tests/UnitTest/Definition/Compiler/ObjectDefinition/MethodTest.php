<?php

namespace DI\Test\UnitTest\Definition\Compiler\ObjectDefinition;

use DI\Definition\Compiler\ObjectDefinitionCompiler;
use DI\Scope;

/**
 * Tests only the generation of setters
 */
class MethodTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method('setThing');

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2();
$object->setThing();
return $object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testWithParameters()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method(
                'setWithParams',
                \DI\get('DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2'),
                'foo'
            );

        $resolver = new \DI\Definition\Compiler\ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2();
$object->setWithParams(
    $this->get('DI\\Test\\UnitTest\\Definition\\Compiler\\ObjectDefinition\\Fixtures\\Class2'),
    'foo'
);
return $object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testDefaultValues()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method('setWithDefaultValues');

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2();
$object->setWithDefaultValues();
return $object;
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class1::__construct takes 2 parameters, 0 defined or guessed
     */
    public function testWrongNumberOfParameters()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class1');

        $resolver = new ObjectDefinitionCompiler();
        $resolver->compile($entry->getDefinition('class1'));
    }
}
