<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\Source\CombinedDefinitionSource;

/**
 * Test class for CombinedDefinitionSource
 */
class CombinedDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{

    public function testSubSources()
    {
        $source = new CombinedDefinitionSource();
        $this->assertEmpty($source->getSources());

        $source->addSource(new ArrayDefinitionSource());
        $this->assertCount(1, $source->getSources());

        $source->addSource(new ArrayDefinitionSource());
        $this->assertCount(2, $source->getSources());
    }

    public function testSubSourcesCalled()
    {
        $source = new CombinedDefinitionSource();
        $this->assertEmpty($source->getSources());

        $subSource1 = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $source->addSource($subSource1);

        // The sub source should have its method 'getDefinition' called once
        $subSource1->expects($this->once())->method('getDefinition')
            ->will($this->returnValue(null));

        $subSource2 = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $source->addSource($subSource2);

        // The sub source should have its method 'getDefinition' called once
        $subSource2->expects($this->once())->method('getDefinition')
            ->will($this->returnValue(null));

        $source->getDefinition('foo');
    }

}
