<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;

/**
 * Test object definitions
 *
 * TODO add more tests
 *
 * @coversNothing
 */
class ObjectDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_object_without_autowiring()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions([
            // with the same name
            'stdClass' => \DI\object('stdClass'),
            // with name inferred
            'ArrayObject' => \DI\object()
                ->constructor([]),
            // with a different name
            'object' => \DI\object('ArrayObject')
                ->constructor([]),
        ]);
        $container = $builder->build();

        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
        $this->assertInstanceOf('ArrayObject', $container->get('object'));
        $this->assertInstanceOf('ArrayObject', $container->get('ArrayObject'));
    }
}
