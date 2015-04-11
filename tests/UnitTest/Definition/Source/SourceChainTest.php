<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ClassDefinition;
use DI\Definition\Definition;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\Autowiring;
use DI\Definition\Source\SourceChain;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Source\SourceChain
 */
class SourceChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_get_from_all_sources()
    {
        $chain = new SourceChain(array(
            new DefinitionArray(array(
                'test1' => 'test1',
            )),
            new DefinitionArray(array(
                'test2' => 'test2',
            )),
            new DefinitionArray(array(
                'test3' => 'test3',
            )),
        ));
        $this->assertValueDefinition($chain->getDefinition('test1'), 'test1');
        $this->assertValueDefinition($chain->getDefinition('test2'), 'test2');
        $this->assertValueDefinition($chain->getDefinition('test3'), 'test3');
    }

    /**
     * @test
     */
    public function should_stop_when_definition_found()
    {
        $chain = new SourceChain(array(
            new DefinitionArray(array(
                'foo' => 'bar',
            )),
            new DefinitionArray(array(
                'foo' => 'bim',
            )),
        ));
        $this->assertValueDefinition($chain->getDefinition('foo'), 'bar');
    }

    /**
     * @test
     */
    public function setting_the_mutable_definition_source_should_chain_it_at_the_top()
    {
        $chain = new SourceChain(array(
            new DefinitionArray(array(
                'foo' => 'bar',
            )),
        ));

        $chain->setMutableDefinitionSource(new DefinitionArray(array(
            'foo' => 'bim',
        )));
        $this->assertValueDefinition($chain->getDefinition('foo'), 'bim');
    }

    /**
     * @test
     */
    public function should_get_sub_definitions_with_different_name_from_root()
    {
        $chain = new SourceChain(array(
            new DefinitionArray(array(
                'subdef' => \DI\object('stdClass'),
            )),
            new DefinitionArray(array(
                'def' => \DI\object('subdef'),
            )),
            new Autowiring(),
        ));

        /** @var ClassDefinition $definition */
        $definition = $chain->getDefinition('def');
        $this->assertTrue($definition instanceof ClassDefinition);
        $this->assertEquals('def', $definition->getName());
        $this->assertEquals('subdef', $definition->getClassName());
    }

    private function assertValueDefinition(Definition $definition, $value)
    {
        $this->assertTrue($definition instanceof ValueDefinition);
        /** @var ValueDefinition $definition */
        $this->assertEquals($value, $definition->getValue());
    }
}
