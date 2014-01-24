<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Compiler\DefinitionCompiler;

use DI\Compiler\DefinitionCompiler\ValueDefinitionCompiler;
use DI\Definition\FactoryDefinition;
use DI\Definition\ValueDefinition;

class ValueDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testCompileString()
    {
        $resolver = new ValueDefinitionCompiler();

        $value = $resolver->compile(new ValueDefinition('foo', 'bar'));

        $this->assertEquals("return 'bar';", $value);
    }

    public function testCompileInt()
    {
        $resolver = new ValueDefinitionCompiler();

        $value = $resolver->compile(new ValueDefinition('foo', 15));

        $this->assertEquals("return 15;", $value);
    }

    public function testCompileFloat()
    {
        $resolver = new ValueDefinitionCompiler();

        $value = $resolver->compile(new ValueDefinition('foo', 15.43));

        $this->assertEquals("return 15.43;", $value);
    }

    public function testCompileBool()
    {
        $resolver = new ValueDefinitionCompiler();

        $value = $resolver->compile(new ValueDefinition('foo', true));

        $this->assertEquals("return true;", $value);

        $value = $resolver->compile(new ValueDefinition('foo', false));

        $this->assertEquals("return false;", $value);
    }

    public function testCompileArray()
    {
        $resolver = new ValueDefinitionCompiler();

        $value = $resolver->compile(new ValueDefinition('foo', array(1, 2, 3)));

        $code = "return array (
  0 => 1,
  1 => 2,
  2 => 3,
);";
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Compiler\CompilationException
     * @expectedExceptionMessage Impossible to compile objects to PHP code, use a callback (factory) or a class definition instead
     */
    public function testCompileObject()
    {
        $resolver = new ValueDefinitionCompiler();

        $resolver->compile(new ValueDefinition('foo', new \stdClass()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition compiler is only compatible with ValueDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new FactoryDefinition('foo', function () {
        });
        $resolver = new ValueDefinitionCompiler();

        $resolver->compile($definition);
    }
}
