<?php

namespace MoeMizrak\ValidatorGuardCore\Tests\src\Attributes;

use Illuminate\Support\Carbon;
use MoeMizrak\ValidatorGuardCore\Contracts\ValidationAttributeInterface;
use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;

/**
 * NonPastDate attribute for checking if date parameter is passed or in the future.
 *
 * @attribute NonPastDate
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class NonPastDate implements ValidationAttributeInterface
{
    /**
     * @inheritDoc
     */
    public function handle(MethodContextData $methodContextData): bool
    {
        // Date param passed to the method as second parameter
        $dateParam = Carbon::parse($methodContextData->params[1]);

        return $dateParam->isFuture();
    }
}