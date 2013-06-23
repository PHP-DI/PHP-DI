<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

/**
 * Provides singleton access to the Container
 *
 * @deprecated Usage of this class is discouraged, but left for situations where it's the only solution
 * (e.g. difficult integration in an existing codebase). Will be removed in next major version (4.0).
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ContainerSingleton
{

    /**
     * Singleton instance
     * @var Container
     */
    private static $containerInstance = null;

    /**
     * Returns an instance of the Container
     *
     * @return Container
     */
    public static function getInstance()
    {
        if (self::$containerInstance == null) {
            self::$containerInstance = new Container();
        }
        return self::$containerInstance;
    }

}
