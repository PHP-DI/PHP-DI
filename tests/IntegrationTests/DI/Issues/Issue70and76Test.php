<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Issue;

use DI\Container;

/**
 * @see https://github.com/mnapoli/PHP-DI/issues/70
 * @see https://github.com/mnapoli/PHP-DI/issues/76
 */
class Issue70and76Test extends \PHPUnit_Framework_TestCase
{

    /**
     * Should not throw a "DI definition conflict" exception
     * @test
     */
    public function valueDefinitionShouldOverrideReflectionDefinition()
    {
        $container = new Container();

        $container->set('stdClass', 'foo');
        $this->assertEquals('foo', $container->get('stdClass'));
    }

    /**
     * Should not throw a "DI definition conflict" exception
     * @test
     */
    public function closureDefinitionShouldOverrideReflectionDefinition()
    {
        $container = new Container();

        $container->set('stdClass', function() {
                return 'foo';
            });
        $this->assertEquals('foo', $container->get('stdClass'));
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage DI definition conflict
     */
    public function valueAndClosureDefinitionConflictShouldThrowException()
    {
        $container = new Container();

        $container->set('stdClass', 'foo');
        $container->addDefinitions(['stdClass' => function() {
                return 'foo';
            }]);
        $container->get('stdClass');
    }

}
