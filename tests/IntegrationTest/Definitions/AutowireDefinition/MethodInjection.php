<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\AutowireDefinition;

use DI\Annotation\Inject;

class MethodInjection
{
    /**
     * @var \stdClass
     */
    public $autowiredParameter;

    /**
     * @var Class1
     */
    public $overloadedParameter;

    /**
     * Force the injection of a specific value for the first parameter. (when using annotations).
     * @Inject({"autowiredParameter"="anotherStdClass"})
     */
    public function setFoo(\stdClass $autowiredParameter, Class1 $overloadedParameter)
    {
        $this->autowiredParameter = $autowiredParameter;
        $this->overloadedParameter = $overloadedParameter;
    }
}
