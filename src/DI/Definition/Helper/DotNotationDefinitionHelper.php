<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Helper;

use DI\Definition\DotNotationDefinition;

/**
 * @since 5.1
 * @author Michael Seidel <mvs@albami.de>
 */
class DotNotationDefinitionHelper implements DefinitionHelper
{
    /**
     * @var string
     */
    private $expression;

    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @param string $entryName Container entry name
     *
     * @return StringDefinition
     */
    public function getDefinition($entryName)
    {
        return new DotNotationDefinition($entryName, $this->expression);
    }
}