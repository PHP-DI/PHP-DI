<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\Exception\DefinitionException;

/**
 * Extends an array definition by adding new elements into it.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionExtension extends ArrayDefinition implements MergeableDefinition
{
    /**
     * @return string
     */
    public function getExtendedDefinitionName()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Definition $definition)
    {
        if (! $this->canMerge($definition)) {
            throw new DefinitionException(sprintf(
                'Definition %s tries to add array entries but the previous definition is not an array',
                $this->getName()
            ));
        }

        /** @var ArrayDefinition $definition */

        $newValues = array_merge($definition->getValues(), parent::getValues());
        parent::setValues($newValues);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function canMerge(Definition $definition)
    {
        return $definition instanceof ArrayDefinition;
    }
}
