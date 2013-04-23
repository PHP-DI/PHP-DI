<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\Container;
use IntegrationTests\DI\Fixtures\InheritanceTest\SubClass;

/**
 * Test class for bean injection
 */
class InheritanceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test a dependency is injected if the injection is defined on a parent class
     *
     * @dataProvider containerProvider
     */
    public function testInjectionOnParentClass(Container $container)
    {
        /** @var $instance SubClass */
        $instance = $container->get('IntegrationTests\DI\Fixtures\InheritanceTest\SubClass');

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property3);
    }


    /**
     * PHPUnit data provider: generates container configurations for running the same tests for each configuration possible
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using annotations
        $containerAnnotations = new Container();
        $containerAnnotations->useReflection(true);
        $containerAnnotations->useAnnotations(true);

        // Test with a container using array configuration
        $containerArray = new Container();
        $containerArray->useReflection(true);
        $containerArray->useAnnotations(false);
        $containerArray->addDefinitions(
            array(
                'IntegrationTests\DI\Fixtures\InheritanceTest\BaseClass' => array(
                    'properties'  => array(
                        'property1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'constructor' => array(
                        'param1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'methods'     => array(
                        'setProperty2' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                ),
            )
        );

        return array(
            array($containerAnnotations),
            array($containerArray),
        );
    }

}
