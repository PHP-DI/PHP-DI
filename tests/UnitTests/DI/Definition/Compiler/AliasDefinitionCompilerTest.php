<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Compiler;

use DI\Definition\Compiler\AliasDefinitionCompiler;
use DI\Definition\AliasDefinition;
use DI\Definition\FactoryDefinition;

class AliasDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testCompileString()
    {
        $resolver = new AliasDefinitionCompiler();

        $value = $resolver->compile(new AliasDefinition('foo', 'bar'));

        $this->assertEquals('return $this->get(\'bar\');', $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition compiler is only compatible with AliasDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new FactoryDefinition('foo', function () {
        });
        $resolver = new AliasDefinitionCompiler();

        $resolver->compile($definition);
    }
}
