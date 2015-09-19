<?php

namespace DI\Definition;

/**
 * Definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Definition
{
    /**
     * Returns the name of the entry in the container.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the scope of the entry.
     *
     * @return string
     */
    public function getScope();
}
