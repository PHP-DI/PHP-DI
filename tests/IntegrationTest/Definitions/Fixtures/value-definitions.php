<?php

return array(
    'string' => 'foo',
    'int'    => 123,
    'object' => new stdClass(),
    'helper' => DI\value('foo'),
);
