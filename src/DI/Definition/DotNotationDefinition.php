<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Scope;

/**
 * Definition of a string describing the path to a value or entries in nested arrays via "dot" notation
 *
 * @since 5.1
 * @author Michael Seidel <mvs@albami.de>
 */
class DotNotationDefinition implements Definition
{
    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expression;

    /**
     * @param string $name       Entry name
     * @param string $expression
     */
    public function __construct($name, $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::SINGLETON;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }
}