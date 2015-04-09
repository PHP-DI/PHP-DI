<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\HasSubDefinition;

/**
 * Manages a chain of other definition sources.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SourceChain implements DefinitionSource, MutableDefinitionSource
{
    /**
     * @var DefinitionSource[]
     */
    private $sources;

    /**
     * @var DefinitionSource
     */
    private $rootSource;

    /**
     * @var MutableDefinitionSource|null
     */
    private $mutableSource;

    /**
     * @param DefinitionSource[] $sources
     */
    public function __construct(array $sources)
    {
        // We want a numerically indexed array to ease the traversal later
        $this->sources = array_values($sources);
        $this->rootSource = $this;
    }

    /**
     * {@inheritdoc}
     * @param int $startIndex Use this parameter to start looking from a specific
     *                        point in the source chain.
     */
    public function getDefinition($name, $startIndex = 0)
    {
        for ($i = $startIndex; $i < count($this->sources); $i++) {
            $source = $this->sources[$i];

            $definition = $source->getDefinition($name);

            if ($definition) {
                if ($definition instanceof HasSubDefinition) {
                    $this->resolveSubDefinition($definition, $i);
                }
                return $definition;
            }
        }

        return null;
    }

    public function addDefinition(Definition $definition)
    {
        if (! $this->mutableSource) {
            throw new \LogicException("The container's definition source has not been initialized correctly");
        }

        $this->mutableSource->addDefinition($definition);
    }

    public function setRootDefinitionSource(DefinitionSource $rootSource)
    {
        $this->rootSource = $rootSource;
    }

    private function resolveSubDefinition(HasSubDefinition $definition, $currentIndex)
    {
        $subDefinitionName = $definition->getSubDefinitionName();

        if ($subDefinitionName === $definition->getName()) {
            // Extending itself: look in the next sources only (else infinite recursion)
            $subDefinition = $this->getDefinition($subDefinitionName, $currentIndex + 1);

            if (! $subDefinition) {
                throw new DefinitionException(sprintf(
                    "Definition '%s' extends a non-existing definition",
                    $definition->getName()
                ));
            }
        } else {
            // Extending another definition: look from the root
            $subDefinition = $this->rootSource->getDefinition($subDefinitionName);

            if (! $subDefinition) {
                throw new DefinitionException(sprintf(
                    "Definition '%s' extends a non-existing definition '%s'",
                    $definition->getName(),
                    $subDefinitionName
                ));
            }
        }

        $definition->setSubDefinition($subDefinition);
    }

    public function setMutableDefinitionSource(MutableDefinitionSource $mutableSource)
    {
        $this->mutableSource = $mutableSource;

        array_unshift($this->sources, $mutableSource);
    }
}
