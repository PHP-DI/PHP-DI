<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\ArrayDefinitionReader;
use DI\Definition\CombinedDefinitionReader;

/**
 * Test class for CombinedDefinitionReader
 */
class CombinedDefinitionReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testSubReaders()
    {
        $reader = new CombinedDefinitionReader();
        $this->assertEmpty($reader->getReaders());

        $reader->addReader(new ArrayDefinitionReader());
        $this->assertCount(1, $reader->getReaders());

        $reader->addReader(new ArrayDefinitionReader());
        $this->assertCount(2, $reader->getReaders());
    }

    public function testSubReadersCalled()
    {
        $reader = new CombinedDefinitionReader();
        $this->assertEmpty($reader->getReaders());

        $subReader1 = $this->getMockForAbstractClass('DI\Definition\DefinitionReader');
        $reader->addReader($subReader1);

        // The sub reader should have its method 'getDefinition' called once
        $subReader1->expects($this->once())->method('getDefinition')
            ->will($this->returnValue(null));

        $subReader2 = $this->getMockForAbstractClass('DI\Definition\DefinitionReader');
        $reader->addReader($subReader2);

        // The sub reader should have its method 'getDefinition' called once
        $subReader2->expects($this->once())->method('getDefinition')
            ->will($this->returnValue(null));

        $reader->getDefinition('foo');
    }

}
