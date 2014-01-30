<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Issues;

use DI\ContainerBuilder;
use IntegrationTests\DI\Issues\Issue72\Class1;

/**
 * Test that the manager prioritize correctly the different sources
 *
 * @see https://github.com/mnapoli/PHP-DI/issues/72
 *
 * @coversNothing
 */
class Issue72Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function annotationDefinitionShouldOverrideReflectionDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $container = $builder->build();

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
    public function arrayDefinitionShouldOverrideReflectionDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(false);

        // Override to 'service2' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');

        $container = $builder->build();

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     */
    public function arrayDefinitionShouldOverrideAnnotationDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(true);

        // Override 'service1' to 'service2' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');

        $container = $builder->build();

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     */
    public function arrayDefinitionShouldOverrideAnotherArrayDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);

        // Override 'service1' to 'service2' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');
        // Override 'service2' to 'service3' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions2.php');

        $container = $builder->build();

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('baz', $class1->arg1->foo);
    }

    /**
     * @test
     */
    public function phpDefinitionShouldOverrideArrayDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');
        $container = $builder->build();

        // Override 'service1' to 'service2'
        $container->set(
            'IntegrationTests\DI\Issues\Issue72\Class1',
            \DI\object()
                ->constructor(\DI\link('service2'))
        );

        /** @var Class1 $class1 */
        $class1 = $container->get('IntegrationTests\DI\Issues\Issue72\Class1');

        $this->assertEquals('bar', $class1->arg1->foo);
    }
}
