<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Benchmarks\DI;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Doctrine\Common\Cache\ArrayCache;
use DI\Container;
use DI\MetadataReader\DefaultMetadataReader;
use DI\MetadataReader\CachedMetadataReader;
use Benchmarks\DI\Fixtures\NamedInjection\NamedInjectionBenchClass;
use Benchmarks\DI\Fixtures\PHPDI\PHPDIBenchClass;
use Benchmarks\DI\Fixtures\PHPDILazy\PHPDILazyBenchClass;
use Benchmarks\DI\Fixtures\NewInstance\NewBenchClass;
use Benchmarks\DI\Fixtures\Singleton\SingletonBenchClass;

/**
 * Bench of a dependency injected using a cache
 */
class InjectWithCacheBench extends \PHPBench\BenchCase
{

    /**
     * Run each bench X times
     */
    protected $_iterationNumber = 40000;

    public function setUp()
    {
        $container = Container::getInstance();
        $container->set("myBean", new PHPDIBenchClass());
        $container->setMetadataReader(
            new CachedMetadataReader(
                new DefaultMetadataReader(),
                new ArrayCache(),
                false
            )
        );
    }

    public function benchNew()
    {
        $class = new NewBenchClass();
    }

    public function benchSingleton()
    {
        $class = new SingletonBenchClass();
    }

    public function benchInject()
    {
        $class = new PHPDIBenchClass();
        \DI\Container::getInstance()->injectAll($class);
    }

    public function benchLazyInject()
    {
        $class = new PHPDILazyBenchClass();
        \DI\Container::getInstance()->injectAll($class);
    }

    public function benchNamedInject()
    {
        $class = new NamedInjectionBenchClass();
        \DI\Container::getInstance()->injectAll($class);
    }

}
