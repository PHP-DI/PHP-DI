<?php

namespace DI\Test\UnitTest\Definition\Compiler;

use DI\Definition\Compiler\AliasDefinitionCompiler;
use DI\Definition\AliasDefinition;

class AliasDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testCompileString()
    {
        $resolver = new AliasDefinitionCompiler();

        $value = $resolver->compile(new AliasDefinition('foo', 'bar'));

        $this->assertEquals('return $this->get(\'bar\');', $value);
    }
}
