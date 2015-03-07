<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\AliasDefinition;
use DI\Definition\DefinitionManager;
use DI\Definition\ValueDefinition;
use Doctrine\Common\Cache\ArrayCache;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\DefinitionManager
 */
class DefinitionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_get_from_cache()
    {
        $definitionManager = new DefinitionManager();

        $cache = EasyMock::spy('Doctrine\Common\Cache\Cache', array(
            'fetch' => 'foo',
        ));

        $definitionManager->setCache($cache);

        $this->assertEquals($cache, $definitionManager->getCache());

        $this->assertEquals('foo', $definitionManager->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_to_cache()
    {
        $definitionManager = new DefinitionManager();

        $cache = EasyMock::spy('Doctrine\Common\Cache\Cache', array(
            'fetch' => false,
            'save'  => null,
        ));

        $definitionManager->setCache($cache);

        $this->assertNull($definitionManager->getDefinition('foo'));
    }

    /**
     * @test
     * Tests that the given definition source is chained to the ArraySource and used.
     */
    public function should_get_definitions_from_definition_source()
    {
        $definition = EasyMock::mock('DI\Definition\CacheableDefinition');

        $source = EasyMock::mock('DI\Definition\Source\DefinitionSource');
        $source->expects($this->once())
            ->method('getDefinition')
            ->with('foo')
            ->will($this->returnValue($definition));

        $definitionManager = new DefinitionManager($source);

        $this->assertSame($definition, $definitionManager->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_allow_to_add_definitions()
    {
        $definitionManager = new DefinitionManager();
        $valueDefinition = new ValueDefinition('foo', 'bar');

        $definitionManager->addDefinition($valueDefinition);

        $this->assertSame($valueDefinition, $definitionManager->getDefinition('foo'));
    }

    /**
     * @test
     * @see https://github.com/mnapoli/PHP-DI/issues/222
     */
    public function adding_a_definition_should_clear_the_cached_value()
    {
        $definitionManager = new DefinitionManager();
        $definitionManager->setCache(new ArrayCache());

        $firstDefinition = new AliasDefinition('foo', 'bar');
        $secondDefinition = new AliasDefinition('foo', 'bam');

        $definitionManager->addDefinition($firstDefinition);
        $this->assertSame($firstDefinition, $definitionManager->getDefinition('foo'));

        $definitionManager->addDefinition($secondDefinition);
        $this->assertSame($secondDefinition, $definitionManager->getDefinition('foo'));
    }
}
