<?php

declare(strict_types=1);

namespace DI;

use DI\Definition\Helper\DefinitionHelper;

/**
 * Describes the basic interface of a container.
 *
 * @api
 *
 * @author Daniel Fernando Lourusso <dflourusso@gmail.com>
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    /**
     * Define an object or a value in the container.
     *
     * @param string $name Entry name
     * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
     */
    public function set(string $name, $value);
}
