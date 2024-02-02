<?php

declare(strict_types=1);

namespace DI\Definition\Exception;

use DI\Definition\Definition;
use Psr\Container\ContainerExceptionInterface;

/**
 * Invalid DI definitions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InvalidDefinition extends \Exception implements ContainerExceptionInterface
{
    public static function create(Definition $definition, string $message, \Exception $previous = null) : self
    {
        return new self(sprintf(
            '%s' . "\n" . 'Full definition:' . "\n" . '%s',
            $message,
            (string) $definition
        ), 0, $previous);
    }
}
