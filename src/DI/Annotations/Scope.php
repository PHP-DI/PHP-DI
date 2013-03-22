<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Annotations;

/**
 * "Scope" annotation
 * @Annotation
 * @Target("CLASS")
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
