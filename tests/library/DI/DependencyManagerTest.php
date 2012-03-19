<?php

namespace DI;

use Doctrine\Common\ClassLoader;

require dirname(__FILE__) . '/../../../library/Doctrine/Common/ClassLoader.php';

// Fixtures
require_once dirname(__FILE__) . '/fixtures/Class1.php';
require_once dirname(__FILE__) . '/fixtures/Class2.php';


/**
 * Test class for DependencyManager.
 */
class DependencyManagerTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        // Autoloading
        $doctrineClassLoader = new ClassLoader('Doctrine', dirname(__FILE__) . '/../../../library');
        $doctrineClassLoader->register();
        $diClassLoader = new ClassLoader('DI', dirname(__FILE__) . '/../../../library');
        $diClassLoader->register();
    }

    public function testGetInstance() {
        $instance = DependencyManager::getInstance();
        $this->assertInstanceOf('\DI\DependencyManager', $instance);
        $instance2 = DependencyManager::getInstance();
        $this->assertSame($instance, $instance2);
    }

    public function testResolveDependencies() {
        $class1 = new \Class1();
        $dependency = $class1->getClass2();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('\Class2', $dependency);
    }

    public function testDefaultFactorySingleton() {
        $class1_1 = new \Class1();
        $class2_1 = $class1_1->getClass2();
        $class1_2 = new \Class1();
        $class2_2 = $class1_2->getClass2();
        $this->assertSame($class2_1, $class2_2);
    }

}
