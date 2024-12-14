<?php

namespace MoeMizrak\ValidatorGuardCore\Data;

use Spatie\LaravelData\Data;

class MethodContextData extends Data
{
    public function __construct(
        public mixed $methodResponse = null, // Response of the method so that it can be used for validation.
        public array $params = [] // Parameters of the method so that they can be used for validation.
    ) {}
}
