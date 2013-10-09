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
use DI\Entry;
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
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $container = $builder->build();

        $value = new \stdClass();
        $value->foo = 'bar';
        $container->set('service1', $value);

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     */
    public function arrayDefinitionShouldOverrideAnnotationDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(true);
        $container = $builder->build();

        // Override 'service1' to 'service2'
        $container->addDefinitions(array(
                'service2' => Entry::factory(function() {
                    $value = new \stdClass();
                    $value->foo = 'bar';
                    return $value;
                }),
                Class1::class => Entry::object()
                    ->withConstructor('service1'),
            ));

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     */
    public function simpleDefinitionShouldOverrideArrayDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $container = $builder->build();

        $container->addDefinitions(array(
            'service2' => Entry::factory(function() {
                $value = new \stdClass();
                $value->foo = 'bar';
                return $value;
            }),
            Class1::class => Entry::object()
                ->withConstructor('service1'),
        ));
        // Override 'service1' to 'service2'
        $container->set(Class1::class, Entry::object()
            ->withConstructor('service2'));

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('bar', $class1->arg1->foo);
    }
}
