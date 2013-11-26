<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Compiler\DefinitionCompiler;

use DI\Compiler\DefinitionCompiler\ClassDefinitionCompiler;
use DI\Definition\CallableDefinition;
use DI\Entry;
use DI\Scope;

class ClassDefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testPrototype()
    {
        $entry = Entry::object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->withScope(Scope::PROTOTYPE());

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testSingleton()
    {
        $entry = Entry::object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->withScope(Scope::SINGLETON());

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
return new \DI\Compiler\SharedEntry(\$object);
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition compiler is only compatible with ClassDefinition objects, DI\Definition\CallableDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new CallableDefinition('foo', function () {
        });
        $resolver = new ClassDefinitionCompiler();

        $resolver->compile($definition);
    }
}
