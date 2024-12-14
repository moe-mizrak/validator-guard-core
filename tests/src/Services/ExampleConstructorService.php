<?php

namespace MoeMizrak\ValidatorGuardCore\Tests\src\Services;

use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\Comparison;

/**
 * Example Service for testing constructor parameters.
 */
class ExampleConstructorService
{
    public function __construct(protected int $intValue, protected string $stringValue) {}

    #[Comparison(200, '>')]
    public function comparisonSucceedMethod(int $param): int
    {
        return $param;
    }
}
