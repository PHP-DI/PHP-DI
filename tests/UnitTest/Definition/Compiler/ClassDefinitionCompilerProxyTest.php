<?php

namespace DI\Test\UnitTest\Definition\Compiler;

/**
 * Tests the generation for classes marked as lazy
 */
class ClassDefinitionCompilerProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleProxy()
    {
        $entry = \DI\object('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class2')
            ->lazy();

        $resolver = new \DI\Definition\Compiler\ObjectDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<'PHP'
$resolver = function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) {
    $object = new \DI\Test\UnitTest\Definition\Compiler\Fixtures\Class2();
    $wrappedObject = $object;
    $initializer = null;
    return true;
};
return $this->createProxy('DI\Test\UnitTest\Definition\Compiler\Fixtures\Class2', $resolver);
PHP;
        $this->assertEquals($code, $value);
    }
}
