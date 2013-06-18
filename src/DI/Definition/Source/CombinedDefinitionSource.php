<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\ClassDefinition;
use DI\Definition\ClosureDefinition;
use DI\Definition\Definition;
use DI\Definition\ValueDefinition;

/**
 * A source that merges the definitions of several sub-sources
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CombinedDefinitionSource implements DefinitionSource
{

    /**
     * Sub-sources
     * @var DefinitionSource[]
     */
    private $subSources = array();

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        /** @var $definition Definition|null */
        $definition = null;

        foreach ($this->subSources as $subSource) {
            $subDefinition = $subSource->getDefinition($name);

            if (!$subDefinition) {
                continue;
            }

            $definition = $this->mergeDefinitions($definition, $subDefinition);
        }

        return $definition;
    }

    /**
     * @return DefinitionSource[]
     */
    public function getSources()
    {
        return $this->subSources;
    }

    /**
     * @param DefinitionSource $source
     */
    public function removeSource(DefinitionSource $source)
    {
        foreach ($this->subSources as $key => $subSource) {
            if ($subSource === $source) {
                unset($this->subSources[$key]);
            }
        }
    }

    /**
     * Add a definition source to the stack
     * @param DefinitionSource $source
     */
    public function addSource($source)
    {
        $this->subSources[] = $source;
    }

    private function mergeDefinitions(Definition $definition1 = null, Definition $definition2 = null)
    {
        if ($definition1 === null) {
            return $definition2;
        }
        if ($definition2 === null) {
            return $definition1;
        }

        // A ValueDefinition always prevails on ClassDefinition
        // @see https://github.com/mnapoli/PHP-DI/issues/70
        if ($definition1 instanceof ValueDefinition && $definition2 instanceof ClassDefinition) {
            return $definition1;
        }
        if ($definition1 instanceof ClassDefinition && $definition2 instanceof ValueDefinition) {
            return $definition2;
        }

        // A ClosureDefinition always prevails on ClassDefinition
        // @see https://github.com/mnapoli/PHP-DI/issues/76
        if ($definition1 instanceof ClosureDefinition && $definition2 instanceof ClassDefinition) {
            return $definition1;
        }
        if ($definition1 instanceof ClassDefinition && $definition2 instanceof ClosureDefinition) {
            return $definition2;
        }

        $definition1->merge($definition2);
        return $definition1;
    }

}
