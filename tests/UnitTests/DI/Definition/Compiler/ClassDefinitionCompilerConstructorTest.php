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
use DI\Scope;

/**
 * Tests only the generation of constructors
 */
class ClassDefinitionCompilerConstructorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE());

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testWithParameters()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class1')
            ->scope(Scope::PROTOTYPE())
            ->constructor(\DI\link('UnitTests\DI\Definition\Compiler\Fixtures\Class2'), 'foo');

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class1'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class1(
    \$this->get('UnitTests\\\DI\\\Definition\\\Compiler\\\Fixtures\\\Class2'),
    'foo'
);
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'param1' of UnitTests\DI\Definition\Compiler\Fixtures\Class1::__construct has no value defined or guessable
     */
    public function testWrongNumberOfParameters()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class1')
            ->scope(Scope::PROTOTYPE());

        $resolver = new ClassDefinitionCompiler();
        $resolver->compile($entry->getDefinition('class1'));
    }
}
