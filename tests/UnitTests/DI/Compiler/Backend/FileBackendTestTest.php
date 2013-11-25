<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Compiler\Backend;

use DI\Compiler\Backend\FileBackend;
use DI\ContainerInterface;

class FileBackendTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->clearDirectory();
    }

    public function tearDown()
    {
        $this->clearDirectory();
    }

    public function testWrite()
    {
        $backend = new FileBackend(__DIR__ . '/definitions');

        $this->assertFileNotExists(__DIR__ . '/definitions/foo.php');

        $line = "return 'bar';";
        $backend->writeCompiledEntry('foo', $line);

        $this->assertFileExists(__DIR__ . '/definitions/foo.php');
        $code = <<<PHP
<?php
$line

PHP;
        $this->assertEquals($code, file_get_contents(__DIR__ . '/definitions/foo.php'));
    }

    public function testHas()
    {
        $backend = new FileBackend(__DIR__ . '/definitions');

        $this->assertFalse($backend->hasCompiledEntry('foo'));

        $backend->writeCompiledEntry('foo', "return 'bar';");

        $this->assertTrue($backend->hasCompiledEntry('foo'));
    }

    public function testRead()
    {
        /** @var ContainerInterface $container */
        $container = $this->getMockForAbstractClass('DI\ContainerInterface');

        $backend = new FileBackend(__DIR__ . '/definitions');

        $backend->writeCompiledEntry('foo', "return 'bar';");

        $this->assertEquals('bar', $backend->readCompiledEntry('foo', $container));
    }

    /**
     * Tests that we can use $this->get() and $this->has() in the file.
     */
    public function testReadWithContainer()
    {
        $container = $this->getMockForAbstractClass('DI\ContainerInterface');
        $container->expects($this->once())
            ->method('has')
            ->with('bar')
            ->will($this->returnValue(true));
        $container->expects($this->once())
            ->method('get')
            ->with('bar')
            ->will($this->returnValue(42));

        $backend = new FileBackend(__DIR__ . '/definitions');

        $backend->writeCompiledEntry('foo', "return \$this->has('bar') ? \$this->get('bar') : 0;");

        $this->assertEquals(42, $backend->readCompiledEntry('foo', $container));
    }

    /**
     * Tests that the entry name is escaped before being used as a file name.
     */
    public function testEscapeFileName()
    {
        $backend = new FileBackend(__DIR__ . '/definitions');

        $entryName = 'aB1-_.1\\2/3*4$5%6[7]8';
        $escapedName = 'aB1-__1_2_3_4_5_6_7_8';

        $this->assertFileNotExists(__DIR__ . '/definitions/' . $escapedName . '.php');

        $backend->writeCompiledEntry($entryName, "return 'bar';");

        $this->assertFileExists(__DIR__ . '/definitions/' . $escapedName . '.php');
    }

    /**
     * @expectedException \DI\NotFoundException
     * @expectedExceptionMessage No entry or class found for 'foo'
     */
    public function testReadUnknownEntry()
    {
        /** @var ContainerInterface $container */
        $container = $this->getMockForAbstractClass('DI\ContainerInterface');

        $backend = new FileBackend(__DIR__ . '/definitions');

        $backend->readCompiledEntry('foo', $container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The path /foobar is not writable, impossible to use it to store the compiled container
     */
    public function testNotWritablePath()
    {
        // Please don't have a /foobar directory ;)
        new FileBackend('/foobar');
    }

    private function clearDirectory()
    {
        // Clear all files in directory
        foreach (glob(__DIR__ . '/definitions/*.php') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
