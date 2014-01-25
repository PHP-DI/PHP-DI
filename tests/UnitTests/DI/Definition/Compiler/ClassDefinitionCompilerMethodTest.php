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
 * Tests only the generation of setters
 */
class ClassDefinitionCompilerMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method('setThing');

        $resolver = new \DI\Definition\Compiler\ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
\$object->setThing();
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testWithParameters()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method(
                'setWithParams',
                \DI\link('UnitTests\DI\Definition\Compiler\Fixtures\Class2'),
                'foo'
            );

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
\$object->setWithParams(
    \$this->get('UnitTests\\\DI\\\Definition\\\Compiler\\\Fixtures\\\Class2'),
    'foo'
);
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    /**
     * Check that injecting "null" is supported
     */
    public function testWithNullParameters()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method('setWithParams', null, null);

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
\$object->setWithParams(
    NULL,
    NULL
);
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testDefaultValues()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->scope(Scope::PROTOTYPE())
            ->method('setWithDefaultValues');

        $resolver = new \DI\Definition\Compiler\ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
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
     * @expectedExceptionMessage The parameter 'param1' of UnitTests\DI\Definition\Compiler\Fixtures\Class1::__construct has no value defined or guessable
     */
    public function testUndefinedParameter()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class1');

        $resolver = new \DI\Definition\Compiler\ClassDefinitionCompiler();
        $resolver->compile($entry->getDefinition('class1'));
    }
}
