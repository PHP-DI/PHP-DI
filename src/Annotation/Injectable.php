<?php

declare(strict_types=1);

namespace DI\Annotation;

/**
 * "Injectable" annotation.
 *
 * Marks a class as injectable
 *
 * @api
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
final class Injectable
{
    /**
     * Should the object be lazy-loaded.
     */
    private ?bool $lazy = null;

    public function __construct(array $values)
    {
        if (isset($values['lazy'])) {
            $this->lazy = (bool) $values['lazy'];
        }
    }

    public function isLazy() : ?bool
    {
        return $this->lazy;
    }
}
