<?php

namespace DI\Test\IntegrationTest\Application;

use DI\Application\Kernel;
use DI\Test\IntegrationTest\Application\Fixture\PuliFactoryClass;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\InMemoryDiscovery;
use Puli\Repository\InMemoryRepository;
use Puli\Repository\Resource\FileResource;

/**
 * @covers \DI\Application\Kernel
 */
class KernelTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!defined('PULI_FACTORY_CLASS')) {
            define('PULI_FACTORY_CLASS', 'DI\Test\IntegrationTest\Application\Fixture\PuliFactoryClass');
        }

        PuliFactoryClass::$repository = new InMemoryRepository();
        PuliFactoryClass::$discovery = new InMemoryDiscovery();
    }

    /**
     * @test
     */
    public function creates_a_container()
    {
        $this->assertInstanceOf('DI\Container', (new Kernel)->createContainer());
    }

    /**
     * @test
     */
    public function registers_puli_repository()
    {
        $container = (new Kernel)->createContainer();
        $this->assertInstanceOf('Puli\Repository\Api\ResourceRepository', $container->get('Puli\Repository\Api\ResourceRepository'));
    }

    /**
     * @test
     */
    public function registers_puli_discovery()
    {
        $container = (new Kernel)->createContainer();
        $this->assertInstanceOf('Puli\Discovery\Api\Discovery', $container->get('Puli\Discovery\Api\Discovery'));
    }

    /**
     * @test
     */
    public function registers_module_configuration_files()
    {
        $this->createPuliResource('/blog/config.php', __DIR__ . '/Fixture/config.php');
        $this->bindPuliResource('/blog/config.php', Kernel::PULI_BINDING_NAME);

        $container = (new Kernel)->createContainer();
        $this->assertEquals('bar', $container->get('foo'));
    }

    private function createPuliResource($path, $file)
    {
        PuliFactoryClass::$repository->add($path, new FileResource($file));
    }

    private function bindPuliResource($path, $bindingName)
    {
        PuliFactoryClass::$discovery->addBindingType(new BindingType($bindingName));

        $binding = new ResourceBinding($path, $bindingName);
        $binding->setRepository(PuliFactoryClass::$repository);
        PuliFactoryClass::$discovery->addBinding($binding);
    }
}
