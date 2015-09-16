<?php

namespace DI\Definition\Compiler;

use DI\Definition\Definition;

/**
 * Dispatches to more specific compilers.
 *
 * Dynamic dispatch pattern.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CompilerDispatcher implements DefinitionCompiler
{
    private $valueCompiler;
    private $arrayCompiler;
    private $factoryCompiler;
    private $decoratorCompiler;
    private $aliasCompiler;
    private $objectCompiler;
    private $envVariableCompiler;
    private $stringCompiler;

    public function compile(Definition $definition)
    {
        $definitionCompiler = $this->getDefinitionCompiler($definition);

        return $definitionCompiler->compile($definition);
    }

    /**
     * Returns a resolver capable of handling the given definition.
     *
     * @param Definition $definition
     *
     * @throws \RuntimeException No definition resolver was found for this type of definition.
     * @return DefinitionCompiler
     */
    private function getDefinitionCompiler(Definition $definition)
    {
        switch (true) {
            case ($definition instanceof \DI\Definition\ObjectDefinition):
                if (! $this->objectCompiler) {
                    $this->objectCompiler = new ObjectDefinitionCompiler();
                }
                return $this->objectCompiler;
            case ($definition instanceof \DI\Definition\ValueDefinition):
                if (! $this->valueCompiler) {
                    $this->valueCompiler = new ValueDefinitionCompiler();
                }
                return $this->valueCompiler;
            case ($definition instanceof \DI\Definition\AliasDefinition):
                if (! $this->aliasCompiler) {
                    $this->aliasCompiler = new AliasDefinitionCompiler();
                }
                return $this->aliasCompiler;
            case ($definition instanceof \DI\Definition\DecoratorDefinition):
                if (! $this->decoratorCompiler) {
                    $this->decoratorCompiler = new DecoratorDefinitionCompiler();
                }
                return $this->decoratorCompiler;
            case ($definition instanceof \DI\Definition\FactoryDefinition):
                if (! $this->factoryCompiler) {
                    $this->factoryCompiler = new FactoryDefinitionCompiler();
                }
                return $this->factoryCompiler;
            case ($definition instanceof \DI\Definition\ArrayDefinition):
                if (! $this->arrayCompiler) {
                    $this->arrayCompiler = new ArrayDefinitionCompiler();
                }
                return $this->arrayCompiler;
            case ($definition instanceof \DI\Definition\EnvironmentVariableDefinition):
                if (! $this->envVariableCompiler) {
                    $this->envVariableCompiler = new EnvironmentVariableDefinitionCompiler();
                }
                return $this->envVariableCompiler;
            case ($definition instanceof \DI\Definition\StringDefinition):
                if (! $this->stringCompiler) {
                    $this->stringCompiler = new StringDefinitionCompiler();
                }
                return $this->stringCompiler;
            default:
                throw new \RuntimeException('No definition compiler was configured for definition of type ' . get_class($definition));
        }
    }
}
