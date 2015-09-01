<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\AnnotationsTest\A;
use DI\Test\IntegrationTest\Fixtures\AnnotationsTest\B;
use DI\Test\IntegrationTest\Fixtures\AnnotationsTest\C;
use DI\Test\IntegrationTest\Fixtures\AnnotationsTest\D;

/**
 * Test using annotations.
 *
 * @coversNothing
 */
class AnnotationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function inject_in_properties()
    {
        $container = $this->createContainer();
        /** @var B $object */
        $object = $container->get('DI\Test\IntegrationTest\Fixtures\AnnotationsTest\B');
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);
    }

    /**
     * @test
     */
    public function inject_in_parent_properties()
    {
        $container = $this->createContainer();

        /** @var C $object */
        $object = $container->get('DI\Test\IntegrationTest\Fixtures\AnnotationsTest\C');
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);

        /** @var D $object */
        $object = $container->get('DI\Test\IntegrationTest\Fixtures\AnnotationsTest\D');
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);
    }

    private function createContainer()
    {
        $builder = new ContainerBuilder;
        $builder->useAnnotations(true);
        return $builder->build();
    }
}
