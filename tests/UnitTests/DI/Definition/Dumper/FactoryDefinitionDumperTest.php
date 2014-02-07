<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Dumper;

use DI\Definition\Dumper\FactoryDefinitionDumper;
use DI\Definition\FactoryDefinition;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Dumper\FactoryDefinitionDumper
 */
class FactoryDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $definition = new FactoryDefinition('foo', 'bar');
        $resolver = new FactoryDefinitionDumper();

        $this->assertEquals('Factory', $resolver->dump($definition));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with FactoryDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new FactoryDefinitionDumper();

        $resolver->dump($definition);
    }
}
