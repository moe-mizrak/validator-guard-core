<?php

namespace MoeMizrak\ValidatorGuardCore\Data;

use Spatie\LaravelData\Data;

/**
 * MethodContextData is for method related context data such as method result and parameter
 *
 * @class MethodContextData
 */
final class MethodContextData extends Data
{
    public function __construct(
        public mixed $methodResult = null, // Result of the method so that it can be used for validation.
        public array $params = [] // Parameters of the method so that they can be used for validation.
    ) {}
}