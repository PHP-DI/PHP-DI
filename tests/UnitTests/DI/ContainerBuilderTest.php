<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\ContainerBuilder;
use DI\DefaultInjector;

/**
 * Test class for ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultConfiguration()
    {
        $builder = new ContainerBuilder();
        $container = $builder->build();

        $this->assertNull($container->getDefinitionManager()->getCache());
        $this->assertFalse($container->getDefinitionManager()->getDefinitionsValidation());

        /** @var DefaultInjector $injector */
        $injector = $container->getInjector();
        $this->assertInstanceOf(DefaultInjector::class, $injector);
        $this->assertSame($container, $injector->getContainer());
    }

    public function testSetCache()
    {
        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');

        $builder = new ContainerBuilder();
        $builder->setDefinitionCache($cache);

        $container = $builder->build();

        $this->assertSame($cache, $container->getDefinitionManager()->getCache());
    }

    public function testSetDefinitionsValidation()
    {
        $builder = new ContainerBuilder();
        $builder->setDefinitionsValidation(true);

        $container = $builder->build();

        $this->assertTrue($container->getDefinitionManager()->getDefinitionsValidation());
    }

    public function testWrapContainer()
    {
        $otherContainer = new \stdClass();

        $builder = new ContainerBuilder();
        $builder->wrapContainer($otherContainer);

        $container = $builder->build();

        /** @var DefaultInjector $injector */
        $injector = $container->getInjector();

        $this->assertInstanceOf(DefaultInjector::class, $injector);
        $this->assertSame($otherContainer, $injector->getContainer());
    }

    public function testFluentInterface()
    {
        $builder = new ContainerBuilder();

        $result = $builder->useAnnotations(false);
        $this->assertSame($builder, $result);

        $result = $builder->useAnnotations(true);
        $this->assertSame($builder, $result);

        $result = $builder->useReflection(false);
        $this->assertSame($builder, $result);

        $result = $builder->useReflection(true);
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(true, 'somedir');
        $this->assertSame($builder, $result);

        $result = $builder->writeProxiesToFile(false);
        $this->assertSame($builder, $result);

        $result = $builder->setDefinitionsValidation(true);
        $this->assertSame($builder, $result);

        $result = $builder->setDefinitionsValidation(false);
        $this->assertSame($builder, $result);

        $mockCache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $result = $builder->setDefinitionCache($mockCache);
        $this->assertSame($builder, $result);

        $result = $builder->wrapContainer(null);
        $this->assertSame($builder, $result);
    }

}
