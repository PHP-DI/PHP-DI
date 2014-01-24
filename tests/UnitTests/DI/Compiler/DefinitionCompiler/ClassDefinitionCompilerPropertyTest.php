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
 * Tests only the generation of properties
 */
class ClassDefinitionCompilerPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testPublicProperty()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class3')
            ->scope(Scope::PROTOTYPE())
            ->property('publicProperty', 'foo');

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class3'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class3();
\$object->publicProperty = 'foo';
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }

    public function testPrivateProperty()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class3')
            ->scope(Scope::PROTOTYPE())
            ->property('privateProperty', 'foo');

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class3'));

        $code = <<<PHP
\$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class3();
\$property = new ReflectionProperty('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class3', 'privateProperty');
\$property->setAccessible(true);
\$property->setValue(\$object, 'foo');
return \$object;
PHP;
        $this->assertEquals($code, $value);
    }
}
