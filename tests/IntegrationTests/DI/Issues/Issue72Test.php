<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Issues;

use DI\Container;
use DI\Definition\DefinitionManager;
use IntegrationTests\DI\Issues\Issue72\Class1;

/**
 * @see https://github.com/mnapoli/PHP-DI/issues/72
 */
class Issue72Test extends \PHPUnit_Framework_TestCase
{

    /**
     * Test that the manager prioritize correctly the different sources
     * @test
     */
    public function arrayDefinitionShouldOverrideReflectionDefinition()
    {
        $container = new Container();
        $container->useReflection(true);
        $container->useAnnotations(false);
        $container->addDefinitions(array(
                'service1' => function() {
                    $value = new \stdClass();
                    $value->foo = 'bar';
                    return $value;
                },
                'IntegrationTests\DI\Issues\Issue72\Class1' => array(
                    'constructor' => array('arg1' => 'service1'),
                ),
            ));

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('bar', $class1->arg1->foo);
    }

}
