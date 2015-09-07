<?php

namespace DI\Test\UnitTest\Definition\Compiler;

use DI\Definition\Compiler\ObjectDefinitionCompiler;
use DI\Scope;

/**
 * Tests only the generation of properties
 */
class ClassDefinitionCompilerPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testPublicProperty()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class3')
            ->scope(Scope::PROTOTYPE())
            ->property('publicProperty', 'foo');

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class3'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\Fixtures\Class3();
$object->publicProperty = 'foo';
return $object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testPrivateProperty()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class3')
            ->scope(Scope::PROTOTYPE())
            ->property('privateProperty', 'foo');

        $resolver = new ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class3'));

        $code = <<<'PHP'
$object = new \DI\Test\UnitTest\Definition\Compiler\Fixtures\Class3();
$property = new ReflectionProperty('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class3', 'privateProperty');
$property->setAccessible(true);
$property->setValue($object, 'foo');
return $object;
PHP;
        $this->assertEquals($code, $value);
    }
}
