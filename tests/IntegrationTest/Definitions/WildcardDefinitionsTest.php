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
use DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Issue1;

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
        $builder->addDefinitions(array(
            'foo*'                                 => 'bar',
            'DI\Test\IntegrationTest\*\Interface*' => \DI\object('DI\Test\IntegrationTest\*\Implementation*'),
        ));
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo1'));

        $object = $container->get('DI\Test\IntegrationTest\Fixtures\Interface1');
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\Implementation1', $object);
    }
}
