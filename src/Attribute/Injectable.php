<?php

declare(strict_types=1);

namespace DI\Attribute;

use Attribute;

/**
 * "Injectable" annotation.
 *
 * Marks a class as injectable
 *
 * @api
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Injectable
{
    /**
     * Should the object be lazy-loaded.
     */
    private ?bool $lazy = null;

    public function __construct(?bool $lazy = null)
    {
        $this->lazy = $lazy;
    }

    public function isLazy() : ?bool
    {
        return $this->lazy;
    }
}
