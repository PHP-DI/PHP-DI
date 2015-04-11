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
    public function adding_definitions_should_go_in_the_mutable_definition_source()
    {
        $chain = new SourceChain(array());
        $mutableSource = new DefinitionArray();
        $chain->setMutableDefinitionSource($mutableSource);

        $chain->addDefinition(new ValueDefinition('foo', 'bar'));

        $this->assertValueDefinition($chain->getDefinition('foo'), 'bar');
        $this->assertSame($mutableSource->getDefinition('foo'), $chain->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function search_sub_definitions_with_different_name_from_root()
    {
        $chain = new SourceChain(array(
            new DefinitionArray(array(
                'subdef' => \DI\object('stdClass')
                    ->lazy(),
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
        $this->assertTrue($definition->isLazy());

        // Define a new root source: should be used
        $chain->setRootDefinitionSource(new DefinitionArray(array(
            'subdef' => \DI\object('stdClass'), // this one is not lazy
        )));
        $definition = $chain->getDefinition('def');
        $this->assertFalse($definition->isLazy()); // shouldn't be lazy
    }

    /**
     * @test
     */
    public function search_sub_definitions_with_same_name_from_next_source()
    {
        $chain = new SourceChain(array(
            new DefinitionArray(array(
                'def' => \DI\object(),
            )),
            new DefinitionArray(array(
                'def' => \DI\object('stdClass') // Should use this definition
                    ->lazy(),
            )),
            new DefinitionArray(array(
                'def' => \DI\object('DateTime'), // Should NOT use this one
            )),
            new Autowiring(),
        ));

        /** @var ClassDefinition $definition */
        $definition = $chain->getDefinition('def');
        $this->assertTrue($definition instanceof ClassDefinition);
        $this->assertEquals('def', $definition->getName());
        $this->assertEquals('stdClass', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Definition 'def' extends a non-existing definition 'subdef'
     */
    public function errors_if_extending_an_unknown_different_definition()
    {
        $chain = new SourceChain(array(
            new DefinitionArray(array(
                'def' => \DI\object('subdef'),
            )),
        ));
        $chain->getDefinition('def');
    }

    private function assertValueDefinition(Definition $definition, $value)
    {
        $this->assertTrue($definition instanceof ValueDefinition);
        /** @var ValueDefinition $definition */
        $this->assertEquals($value, $definition->getValue());
    }
}
