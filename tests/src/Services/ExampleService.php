<?php

namespace MoeMizrak\ValidatorGuardCore\Tests\src\Services;

use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\Comparison;
use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\Callback;
use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\NonPastDate;

/**
 * Example Service for testing different scenarios.
 */
class ExampleService
{
    #[Comparison(20, '>')]
    public function comparisonFailedMethod(): int
    {
        return 123;
    }

    #[Comparison(290, '>')]
    public function comparisonSucceedMethod(int $param): int
    {
        return $param;
    }

    #[NonPastDate]
    public function nonPastDateMethod(int $param, string $dateParam): string
    {
        return $param . ' / ' . $dateParam;
    }

    #[Callback(className: ExampleService::class, methodName: 'callbackMethod', params: [33])]
    public function callbackFailedMethod(): string
    {
        return 'callbackFailedMethod response';
    }

    #[Callback(className: ExampleService::class, methodName: 'callbackMethod', params: [300])]
    public function callbackSucceedMethod(): string
    {
        return 'callbackSucceedMethod response';
    }

    public function callbackMethod(int $a): int
    {
        return $a;
    }

    #[Callback(className: ExampleService::class, methodName: 'invalidMethod', params: [300])]
    public function callbackInvalidMethod(): string
    {
        return 'callbackInvalidMethod response';
    }

    #[Comparison(20, '<')]
    #[NonPastDate]
    public function multipleAttributeMethod(int $param, string $dateParam)
    {
        return $param;
    }
}