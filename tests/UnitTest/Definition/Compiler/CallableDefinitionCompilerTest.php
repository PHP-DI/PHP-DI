<?php

namespace DI\Test\UnitTest\Definition\Compiler;

use DI\Definition\Compiler\FactoryDefinitionCompiler;
use DI\Container;
use DI\Definition\FactoryDefinition;

class FactoryDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayCallable()
    {
        $resolver = new FactoryDefinitionCompiler();

        $value = $resolver->compile(new FactoryDefinition('entry', array('foo', 'bar')));

        $code = <<<'PHP'
$factory = array('foo', 'bar');
return $factory($this);
PHP;

        $this->assertEquals($code, $value);
    }

    public function testSimpleClosure()
    {
        $resolver = new FactoryDefinitionCompiler();

        $value = $resolver->compile(new FactoryDefinition('entry', function () {
            return 'bar';
        }));

        $code = <<<'PHP'
$factory = function () {
    return 'bar';
};
return $factory($this);
PHP;

        $this->assertEquals($code, $value);
    }

    public function testClosureWithParameters()
    {
        $resolver = new FactoryDefinitionCompiler();

        $value = $resolver->compile(new FactoryDefinition('entry', function (Container $c) {
                return $c->get('bar');
            }));

        $code = <<<'PHP'
$factory = function (\DI\Container $c) {
    return $c->get('bar');
};
return $factory($this);
PHP;

        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage Unable to compile entry 'entry' because the closure has a 'use ($var)' statement
     */
    public function testClosureWithUse()
    {
        $resolver = new FactoryDefinitionCompiler();

        $resolver->compile(new FactoryDefinition('entry', function (Container $c) use ($resolver) {
            return $c->get('bar');
        }));
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage The callable definition for entry 'foo' must be a closure or an array of strings (no object in the array)
     */
    public function testArrayWithObject()
    {
        $resolver = new FactoryDefinitionCompiler();

        $resolver->compile(new FactoryDefinition('foo', array($this, 'foo')));
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage Unable to compile entry 'foo', a factory must be a callable (closure or array)
     */
    public function testString()
    {
        $resolver = new FactoryDefinitionCompiler();

        $resolver->compile(new FactoryDefinition('foo', 'bar'));
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage Unable to compile entry 'foo', a factory must be a callable (closure or array)
     */
    public function testObject()
    {
        $resolver = new FactoryDefinitionCompiler();

        $resolver->compile(new FactoryDefinition('foo', new \stdClass()));
    }
}
