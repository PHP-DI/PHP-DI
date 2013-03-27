<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Annotation;

/**
 * "Scope" annotation
 * @Annotation
 * @Target("CLASS")
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
final class Scope
{

    /**
     * The scope of an object: prototype, singleton
     * @var Scope|null
     */
    public $value;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->value = new \DI\Scope($values['value']);
        }
    }

}
