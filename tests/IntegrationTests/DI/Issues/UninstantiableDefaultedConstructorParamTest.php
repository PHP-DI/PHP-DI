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
 * Test for constructor injection of parameters that are optional, and use an
 * interface (or other uninstantiable) type hint.
 *
 * @coversNothing Because integration test
 */
class UninstantiableDefaultedConstructorParamTest extends \PHPUnit_Framework_TestCase
{
    public function testIssue()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(false);
        $builder->addDefinitions(__DIR__ . '/../Fixtures/definitions.php');
        $container = $builder->build();
        $service = $container->get('IntegrationTests\DI\Fixtures\Interface2');

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class3', $service);
    }
}
