<?php

namespace MoeMizrak\ValidatorGuardCore\Contracts;

use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;

/**
 * Interface for validation attributes
 *
 * @Interface
 */
interface ValidationAttributeInterface
{
    /**
     * Handles the validation logic for attributes.
     *
     *
     * @return bool - Returns true if the validation passes, false otherwise.
     */
    public function handle(MethodContextData $methodContextData): bool;
}
