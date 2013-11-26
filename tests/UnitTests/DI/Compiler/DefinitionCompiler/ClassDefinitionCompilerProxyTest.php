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

/**
 * Tests the generation for classes marked as lazy
 */
class ClassDefinitionCompilerProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleProxy()
    {
        $entry = \DI\object('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2')
            ->lazy();

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$resolver = function (& \$wrappedObject, \$proxy, \$method, \$parameters, & \$initializer) {
    \$object = new \UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2();
    \$wrappedObject = \$object;
    \$initializer = null;
    return true;
};
return \$this->createProxy('UnitTests\DI\Compiler\DefinitionCompiler\Fixtures\Class2', \$resolver);
PHP;
        $this->assertEquals($code, $value);
    }
}
