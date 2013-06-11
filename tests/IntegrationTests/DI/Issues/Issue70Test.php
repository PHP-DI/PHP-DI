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
 */
class Issue70Test extends \PHPUnit_Framework_TestCase
{

    public function testConstructorNonTypeHintedMethod()
    {
        $container = new Container();

        $container->set('stdClass', 'foo');
        $container->get('stdClass');
    }

}
