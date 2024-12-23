<?php

namespace MoeMizrak\ValidatorGuardCore\Tests\src\Attributes;

use MoeMizrak\ValidatorGuardCore\Contracts\ValidationAttributeInterface;
use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;

/**
 * Comparison attribute for comparing the result of a method with a numerical value
 *
 * @attribute Comparison
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Comparison implements ValidationAttributeInterface
{
    public function __construct(
        public float $value,
        public string $operator = '<=',
    ) {
        // Check the provided operator
        if (! in_array($this->operator, ['<', '<=', '==', '>', '>=', '!='], true)) {
            throw new \InvalidArgumentException("Invalid comparison operator '{$this->operator}'.");
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(MethodContextData $methodContextData): bool
    {
        return match ($this->operator) {
            '<'     => $this->value < $methodContextData->methodResult,
            '<='    => $this->value <= $methodContextData->methodResult,
            '=='    => $this->value == $methodContextData->methodResult,
            '>'     => $this->value > $methodContextData->methodResult,
            '>='    => $this->value >= $methodContextData->methodResult,
            '!='    => $this->value != $methodContextData->methodResult,
            default => false,
        };
    }
}