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
use DI\Entry;
use DI\Scope;

/**
 * Tests only the generation of setters
 */
class ClassDefinitionCompilerMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $entry = Entry::object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->withScope(Scope::PROTOTYPE())
            ->withMethod('setThing');

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
        $entry = Entry::object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->withScope(Scope::PROTOTYPE())
            ->withMethod(
                'setWithParams',
                Entry::link('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2'),
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
        $entry = Entry::object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->withScope(Scope::PROTOTYPE())
            ->withMethod('setWithDefaultValues');

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
\$object->setWithDefaultValues();
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
        $entry = Entry::object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class1');

        $resolver = new ClassDefinitionCompiler();
        $resolver->compile($entry->getDefinition('class1'));
    }
}
