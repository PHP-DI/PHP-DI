<?php

namespace DI\Test\UnitTest\Definition\Compiler\ObjectDefinition;

use DI\Definition\Compiler\ObjectDefinitionCompiler;
use DI\Scope;

class ObjectDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testPrototype()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE);

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2();
return $object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testSingleton()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2')
            ->scope(Scope::SINGLETON);

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures\Class2();
return new \DI\Compiler\SharedEntry($object);
PHP;
        $this->assertEquals($code, $value);
    }
}
