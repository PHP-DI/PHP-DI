<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;

abstract class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    const COMPILED_FILE = __DIR__ . '/tmp/compiled.php';

    public function setUp()
    {
        parent::setUp();

        if (file_exists(self::COMPILED_FILE)) {
            unlink(self::COMPILED_FILE);
        }
    }

    public function provideBuilder()
    {
        return [
            'normal' => [new ContainerBuilder],
            'compiled' => [(new ContainerBuilder)->compile(self::COMPILED_FILE)],
        ];
    }
}
