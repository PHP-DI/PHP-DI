<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;

/**
 * Test caching.
 *
 * @coversNothing
 */
class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function cached_definitions_should_be_overridables()
    {
        $builder = new ContainerBuilder();
        $builder->setDefinitionCache(new ArrayCache());
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');

        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo'));

        $container->set('foo', 'hello');

        $this->assertEquals('hello', $container->get('foo'));
    }
}
