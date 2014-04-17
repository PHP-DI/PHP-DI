<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;

/**
 * Tests error messages at a higher level than unit test.
 *
 * @coversNothing
 */
class ErrorMessagesTest extends \PHPUnit_Framework_TestCase
{
    public function testNonInstantiableClass()
    {
        $message = <<<'MESSAGE'
Entry IntegrationTests\DI\ErrorMessagesFixture2 cannot be resolved: IntegrationTests\DI\ErrorMessagesFixture2 is not instantiable
Definition of IntegrationTests\DI\ErrorMessagesFixture2:
Object (
    class = #NOT INSTANTIABLE# IntegrationTests\DI\ErrorMessagesFixture2
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $container = ContainerBuilder::buildDevContainer();
        $container->get('IntegrationTests\DI\ErrorMessagesFixture2');
    }

    public function testUndefinedMethodParameter()
    {
        $message = <<<'MESSAGE'
Entry IntegrationTests\DI\ErrorMessagesFixture1 cannot be resolved: The parameter 'bar' of IntegrationTests\DI\ErrorMessagesFixture1::__construct has no value defined or guessable
Definition of IntegrationTests\DI\ErrorMessagesFixture1:
Object (
    class = IntegrationTests\DI\ErrorMessagesFixture1
    scope = singleton
    lazy = false
    __construct(
        $foo = 'some value'
        $bar = #UNDEFINED#
        $default = (default value) 123
    )
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $container = ContainerBuilder::buildDevContainer();
        $container->set(
            'IntegrationTests\DI\ErrorMessagesFixture1',
            \DI\object()->constructorParameter('foo', 'some value')
        );

        $container->get('IntegrationTests\DI\ErrorMessagesFixture1');
    }
}

class ErrorMessagesFixture1
{
    public function __construct($foo, $bar, $default = 123)
    {
    }
}

interface ErrorMessagesFixture2
{
}
