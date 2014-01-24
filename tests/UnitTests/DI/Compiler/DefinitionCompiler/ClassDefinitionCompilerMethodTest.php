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
 * Tests only the generation of setters
 */
class ClassDefinitionCompilerMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method('setThing');

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
\$object->setThing();
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testWithParameters()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method(
                'setWithParams',
                \DI\link('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2'),
                'foo'
            );

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
\$object->setWithParams(
    \$this->get('UnitTests\\\DI\\\Compiler\\\DefinitionCompiler\\\Fixtures\\\Class2'),
    'foo'
);
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testDefaultValues()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method('setWithDefaultValues');

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
\$object->setWithDefaultValues(
    'foo',
    'bar'
);
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'param1' of UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class1::__construct has no value defined or guessable
     */
    public function testUndefinedParameter()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class1');

        $resolver = new ClassDefinitionCompiler();
        $resolver->compile($entry->getDefinition('class1'));
    }
}
