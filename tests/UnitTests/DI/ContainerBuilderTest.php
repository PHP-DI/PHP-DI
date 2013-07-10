<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\ContainerBuilder;

/**
 * Test class for ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultConfiguration()
    {
        $builder = new ContainerBuilder();
        $container = $builder->build();

        $this->assertNull($container->getDefinitionManager()->getCache());
        $this->assertFalse($container->getDefinitionManager()->getDefinitionsValidation());
    }

    public function testSetCache()
    {
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');

        $builder = new ContainerBuilder();
        $builder->setDefinitionCache($cache);

        $container = $builder->build();

        $this->assertSame($cache, $container->getDefinitionManager()->getCache());
    }

    public function testSetDefinitionsValidation()
    {
        $builder = new ContainerBuilder();
        $builder->setDefinitionsValidation(true);

        $container = $builder->build();

        $this->assertTrue($container->getDefinitionManager()->getDefinitionsValidation());
    }

}
