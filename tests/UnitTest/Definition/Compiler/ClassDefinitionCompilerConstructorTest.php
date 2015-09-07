<?php

namespace DI\Test\UnitTest\Definition\Compiler;

use DI\Definition\Compiler\ObjectDefinitionCompiler;
use DI\Scope;

/**
 * Tests only the generation of constructors
 */
class ClassDefinitionCompilerConstructorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE);

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\Fixtures\Class2();
return $object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testWithParameters()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class1')
            ->scope(Scope::PROTOTYPE)
            ->constructor(\DI\get('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class2'), 'foo');

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class1'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\Fixtures\Class1(
    $this->get('DI\\Test\\UnitTest\\Definition\\Compiler\\Fixtures\\Class2'),
    'foo'
);
return $object;
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage DI\Test\UnitTest\Definition\Compiler\Fixtures\Class1::__construct takes 2 parameters, 0 defined or guessed
     */
    public function testWrongNumberOfParameters()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class1')
            ->scope(Scope::PROTOTYPE);

        $resolver = new ObjectDefinitionCompiler();
        $resolver->compile($entry->getDefinition('class1'));
    }
}
