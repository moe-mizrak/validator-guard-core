<?php

use MoeMizrak\ValidatorGuardCore\ValidatorGuardCore;

if (! function_exists('valguard')) {
    /**
     * Creates a new ValidatorGuardCore instance for the given class
     *
     * @param object $class
     *
     * @return ValidatorGuardCore
     */
    function valguard(object $class): ValidatorGuardCore
    {
        return new ValidatorGuardCore($class);
    }
}