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
use DI\Definition\EntryReference;

/**
 * @covers \DI\Definition\EntryReference
 */
class EntryReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $helper = new EntryReference('foo');

        $this->assertSame('foo', $helper->getName());
    }

    public function testGetDefinition()
    {
        $helper = new EntryReference('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof AliasDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame('bar', $definition->getTargetEntryName());
    }
}
