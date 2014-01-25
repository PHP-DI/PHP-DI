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

/**
 * Tests the generation for classes marked as lazy
 */
class ClassDefinitionCompilerProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleProxy()
    {
        $entry = \DI\object('UnitTests\DI\Definition\Compiler\Fixtures\Class2')
            ->lazy();

        $resolver = new ClassDefinitionCompiler();

        $value = $resolver->compile($entry->getDefinition('class2'));

        $code = <<<PHP
\$resolver = function (& \$wrappedObject, \$proxy, \$method, \$parameters, & \$initializer) {
    \$object = new \UnitTests\DI\Definition\Compiler\Fixtures\Class2();
    \$wrappedObject = \$object;
    \$initializer = null;
    return true;
};
return \$this->createProxy('UnitTests\DI\Definition\Compiler\Fixtures\Class2', \$resolver);
PHP;
        $this->assertEquals($code, $value);
    }
}
