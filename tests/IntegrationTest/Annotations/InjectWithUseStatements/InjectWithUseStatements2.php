<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Annotations\InjectWithUseStatements;

use DI\Annotation\Inject;
use DI\Test\IntegrationTest\Annotations\InjectWithUseStatements;

class InjectWithUseStatements2
{
    /**
     * @Inject
     * @var InjectWithUseStatements
     */
    public $dependency;
}
