<?php

namespace MoeMizrak\ValidatorGuardCore\Tests\src\Services;

use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\Comparison;

/**
 * Example Service for testing binding classes in service provider.
 *
 * @class ExampleForBindingService
 */
class ExampleForBindingService
{
    #[Comparison(290, '>')]
    public function comparisonSucceedMethod(int $param): int
    {
        return $param;
    }
}