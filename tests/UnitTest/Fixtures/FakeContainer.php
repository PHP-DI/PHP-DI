<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Fixtures;

use DI\Definition\DefinitionManager;
use DI\Proxy\ProxyFactory;
use Interop\Container\ContainerInterface;

/**
 * Fake container class that exposes all constructor parameters.
 *
 * Used to test the ContainerBuilder.
 */
class FakeContainer
{
    /**
     * @var DefinitionManager
     */
    public $definitionManager;

    /**
     * @var ProxyFactory
     */
    public $proxyFactory;

    /**
     * @var ContainerInterface
     */
    public $wrapperContainer;

    public function __construct(
        DefinitionManager $definitionManager,
        ProxyFactory $proxyFactory,
        ContainerInterface $wrapperContainer = null
    ) {
        $this->definitionManager = $definitionManager;
        $this->proxyFactory = $proxyFactory;
        $this->wrapperContainer = $wrapperContainer;
    }
}
