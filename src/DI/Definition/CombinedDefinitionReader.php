<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * A reader that merges the definitions of several sub-readers
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CombinedDefinitionReader implements DefinitionReader
{

    /**
     * Sub-readers
     * @var DefinitionReader[]
     */
    private $subReaders = array();

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        /** @var $definition Definition|null */
        $definition = null;

        foreach ($this->subReaders as $subReader) {
            $subDefinition = $subReader->getDefinition($name);

            if ($subDefinition) {

                if ($definition === null) {
                    $definition = $subDefinition;
                } else {
                    // Merge the definitions
                    $definition->merge($subDefinition);
                }

            }
        }

        return $definition;
    }

    /**
     * @return DefinitionReader[]
     */
    public function getReaders()
    {
        return $this->subReaders;
    }

    /**
     * Add a definition reader to the stack
     * @param DefinitionReader $reader
     */
    public function addReader($reader)
    {
        $this->subReaders[] = $reader;
    }

}
