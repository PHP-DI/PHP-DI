<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Compiler;

use DI\Definition\Compiler\ClassDefinitionCompiler;
use DI\Definition\FactoryDefinition;
use DI\Scope;

class ClassDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testPrototype()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE());

        $resolver = new \DI\Definition\Compiler\ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testSingleton()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::SINGLETON());

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
return new \DI\Compiler\SharedEntry(\$object);
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition compiler is only compatible with ClassDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new FactoryDefinition('foo', function () {
        });
        $resolver = new \DI\Definition\Compiler\ClassDefinitionCompiler();

        $resolver->compile($definition);
    }
}
