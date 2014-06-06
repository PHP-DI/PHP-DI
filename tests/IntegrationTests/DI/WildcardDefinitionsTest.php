<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\Issue1;

/**
 * Test definitions using wildcards
 *
 * @coversNothing
 */
class WildcardDefinitionsTest extends \PHPUnit_Framework_TestCase
{
    public function testWildcards()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Fixtures/wildcards.php');
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo1'));

        $object = $container->get('IntegrationTests\DI\Fixtures\Interface1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $object);
    }
}
