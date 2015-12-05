<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use Interop\Container\Definition\DefinitionProviderInterface;
use Interop\Container\Definition\Test\AbstractDefinitionCompatibilityTest;

/**
 * Test container-interop standard definitions.
 *
 * @coversNothing
 */
class InteropDefinitionTest extends AbstractDefinitionCompatibilityTest
{
    protected function getContainer(DefinitionProviderInterface $definitionProvider)
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions($definitionProvider);
        return $builder->build();
    }
}
