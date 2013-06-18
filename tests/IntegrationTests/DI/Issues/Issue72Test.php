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
 * Test that the manager prioritize correctly the different sources
 *
 * @see https://github.com/mnapoli/PHP-DI/issues/72
 */
class Issue72Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function annotationDefinitionShouldOverrideReflectionDefinition()
    {
        $container = new Container();
        $container->useReflection(true);
        $container->useAnnotations(true);

        $value = new \stdClass();
        $value->foo = 'bar';
        $container->set('service1', $value);

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     */
    public function arrayDefinitionShouldOverrideAnnotationDefinition()
    {
        $container = new Container();
        $container->useReflection(false);

        $container->useAnnotations(true);
        // Override 'service1' to 'service2'
        $container->addDefinitions(array(
                'service2' => function() {
                    $value = new \stdClass();
                    $value->foo = 'bar';
                    return $value;
                },
                'IntegrationTests\DI\Issues\Issue72\Class1' => array(
                    'constructor' => array('arg1' => 'service2'),
                ),
            ));

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     */
    public function simpleDefinitionShouldOverrideArrayDefinition()
    {
        $container = new Container();
        $container->useReflection(false);
        $container->useAnnotations(false);

        $container->addDefinitions(array(
                'service2' => function() {
                    $value = new \stdClass();
                    $value->foo = 'bar';
                    return $value;
                },
                'IntegrationTests\DI\Issues\Issue72\Class1' => array(
                    'constructor' => array('arg1' => 'service1'),
                ),
            ));
        // Override 'service1' to 'service2'
        $container->set('IntegrationTests\DI\Issues\Issue72\Class1')
            ->withConstructor(array('arg1' => 'service2'));

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('bar', $class1->arg1->foo);
    }

}
