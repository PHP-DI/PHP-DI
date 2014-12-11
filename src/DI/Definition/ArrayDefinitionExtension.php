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
     * Name of the extended definition.
     *
     * @var string
     */
    private $extendedDefinitionName;

    /**
     * @param string $name                   Entry name
     * @param string $extendedDefinitionName Name of the extended definition
     * @param array  $values                 Values to add to the extended array definition
     */
    public function __construct($name, $extendedDefinitionName, array $values)
    {
        $this->extendedDefinitionName = $extendedDefinitionName;

        parent::__construct($name, $values);
    }

    /**
     * @return string
     */
    public function getExtendedDefinitionName()
    {
        return $this->extendedDefinitionName;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Definition $definition)
    {
        if (! $this->canMerge($definition)) {
            $name = $this->getName();
            throw new DefinitionException(sprintf(
                'Definition %s tries to add entries to %s but it is not an array',
                $name,
                ($this->extendedDefinitionName === $name) ? 'its previous definition' : $this->extendedDefinitionName
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
