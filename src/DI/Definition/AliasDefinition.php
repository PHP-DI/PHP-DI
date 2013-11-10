<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Defines an alias from an entry to another
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinition implements Definition
{
    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * Name of the target entry
     * @var string
     */
    private $targetEntryName;

    /**
     * @param string $name            Entry name
     * @param string $targetEntryName Name of the target entry
     */
    public function __construct($name, $targetEntryName)
    {
        $this->name = $name;
        $this->targetEntryName = $targetEntryName;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTargetEntryName()
    {
        return $this->targetEntryName;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Definition $definition)
    {
        throw new \BadMethodCallException("Impossible to merge an AliasDefinition with another definition");
    }

    /**
     * {@inheritdoc}
     */
    public function isCacheable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function isMergeable()
    {
        return false;
    }
}
