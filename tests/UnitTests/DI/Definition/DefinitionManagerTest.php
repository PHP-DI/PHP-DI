<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\DefinitionManager;

/**
 * Test class for DefinitionManager
 */
class DefinitionManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test that the manager prioritize correctly the different sources
     * @test
     */
    public function shouldPrioritizeSources()
    {
        $definitionManager = new DefinitionManager();
        $definitionManager->useReflection(true);
        $definitionManager->useAnnotations(true);
        // TODO
    }

    /**
     * @test
     */
    public function shouldUseCache()
    {
        $definitionManager = new DefinitionManager();

        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue('foo'));

        $definitionManager->setCache($cache);

        $this->assertEquals('foo', $definitionManager->getDefinition('foo'));
    }

}
