<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Issues;

use DI\ContainerBuilder;

/**
 * @see https://github.com/mnapoli/PHP-DI/issues/70
 * @see https://github.com/mnapoli/PHP-DI/issues/76
 */
class Issue70and76Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function valueDefinitionShouldOverrideReflectionDefinition()
    {
        $container = ContainerBuilder::buildDevContainer();

        $container->set('stdClass', 'foo');
        $this->assertEquals('foo', $container->get('stdClass'));
    }

    /**
     * @test
     */
    public function closureDefinitionShouldOverrideReflectionDefinition()
    {
        $container = ContainerBuilder::buildDevContainer();

        $container->set('stdClass', \DI\factory(function () {
            return 'foo';
        }));
        $this->assertEquals('foo', $container->get('stdClass'));
    }
}
