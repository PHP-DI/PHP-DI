<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Fixtures;

use DI\Definition\DefinitionManager;
use Interop\DI\ReadableContainerInterface;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

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
     * @var LazyLoadingValueHolderFactory
     */
    public $proxyFactory;

    /**
     * @var ReadableContainerInterface
     */
    public $wrapperContainer;

    public function __construct(
        DefinitionManager $definitionManager,
        LazyLoadingValueHolderFactory $proxyFactory,
        ReadableContainerInterface $wrapperContainer = null
    ) {
        $this->definitionManager = $definitionManager;
        $this->proxyFactory = $proxyFactory;
        $this->wrapperContainer = $wrapperContainer;
    }
}
