<?php

namespace MoeMizrak\ValidatorGuardCore\Tests\src\Attributes;

use Illuminate\Support\Arr;
use MoeMizrak\ValidatorGuardCore\Contracts\ValidationAttributeInterface;
use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;

/**
 * AllowedValues attribute for validation method params are in given values list.
 *
 * @attribute AllowedValues
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class AllowedValues implements ValidationAttributeInterface
{
    public function __construct(
        protected array $values = [],
        protected int $paramPosition
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(MethodContextData $methodContextData): bool
    {
        $param = Arr::get($methodContextData->params, $this->paramPosition);

        return in_array($param, $this->values);
    }
}