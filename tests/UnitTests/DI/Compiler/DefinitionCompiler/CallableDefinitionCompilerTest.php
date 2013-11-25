<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Compiler\DefinitionCompiler;

use DI\Compiler\DefinitionCompiler\CallableDefinitionCompiler;
use DI\Container;
use DI\Definition\CallableDefinition;
use DI\Definition\ValueDefinition;

class CallableDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayCallable()
    {
        $resolver = new CallableDefinitionCompiler();

        $value = $resolver->compile(new CallableDefinition('entry', array('foo', 'bar')));

        $code = <<<PHP
\$factory = array('foo', 'bar');
return \$factory(\$this);
PHP;

        $this->assertEquals($code, $value);
    }

    public function testSimpleClosure()
    {
        $resolver = new CallableDefinitionCompiler();

        $value = $resolver->compile(new CallableDefinition('entry', function () {
            return 'bar';
        }));

        $code = <<<PHP
\$factory = function () {
    return 'bar';
};
return \$factory(\$this);
PHP;

        $this->assertEquals($code, $value);
    }

    public function testClosureWithParameters()
    {
        $resolver = new CallableDefinitionCompiler();

        $value = $resolver->compile(new CallableDefinition('entry', function (Container $c) {
                return $c->get('bar');
            }));

        $code = <<<PHP
\$factory = function (\DI\Container \$c) {
    return \$c->get('bar');
};
return \$factory(\$this);
PHP;

        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage Unable to compile entry 'entry' because the closure has a 'use ($var)' statement
     */
    public function testClosureWithUse()
    {
        $resolver = new CallableDefinitionCompiler();

        $resolver->compile(new CallableDefinition('entry', function (Container $c) use ($resolver) {
            return $c->get('bar');
        }));
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage The callable definition for entry 'foo' must be a closure or an array of strings (no object in the array)
     */
    public function testArrayWithObject()
    {
        $resolver = new CallableDefinitionCompiler();

        $resolver->compile(new CallableDefinition('foo', array($this, 'foo')));
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage Unable to compile entry 'foo', a factory must be a callable (closure or array)
     */
    public function testString()
    {
        $resolver = new CallableDefinitionCompiler();

        $resolver->compile(new CallableDefinition('foo', 'bar'));
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage Unable to compile entry 'foo', a factory must be a callable (closure or array)
     */
    public function testObject()
    {
        $resolver = new CallableDefinitionCompiler();

        $resolver->compile(new CallableDefinition('foo', new \stdClass()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition compiler is only compatible with CallableDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $resolver = new CallableDefinitionCompiler();

        $resolver->compile(new ValueDefinition('foo', 'bar'));
    }
}
