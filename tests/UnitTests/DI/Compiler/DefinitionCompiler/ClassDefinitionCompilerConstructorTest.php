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
use DI\Scope;

/**
 * Tests only the generation of constructors
 */
class ClassDefinitionCompilerConstructorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->withScope(Scope::PROTOTYPE());

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testWithParameters()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class1')
            ->withScope(Scope::PROTOTYPE())
            ->withConstructor(\DI\link('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2'), 'foo');

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class1'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class1(
    \$this->get('UnitTests\\\DI\\\Compiler\\\DefinitionCompiler\\\Fixtures\\\Class2'),
    'foo'
);
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class1::__construct takes 2 parameters, 0 defined or guessed
     */
    public function testWrongNumberOfParameters()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class1')
            ->withScope(Scope::PROTOTYPE());

        $resolver = new ClassDefinitionCompiler();
        $resolver->compile($entry->getDefinition('class1'));
    }
}
