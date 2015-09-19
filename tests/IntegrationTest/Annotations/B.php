<?php

namespace DI\Test\IntegrationTest\Annotations;

use DI\Annotation\Inject;

class B
{
    /**
     * @Inject
     * @var A
     */
    public $public;

    /**
     * @Inject
     * @var A
     */
    protected $protected;

    /**
     * @Inject
     * @var A
     */
    private $private;

    public function getProtected()
    {
        return $this->protected;
    }

    public function getPrivate()
    {
        return $this->private;
    }
}
