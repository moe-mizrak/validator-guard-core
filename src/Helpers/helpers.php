<?php

use MoeMizrak\ValidatorGuardCore\ValidatorGuardCore;

if (! function_exists('valguard')) {

    /**
     * Creates a new ValidatorGuardCore instance for the given class
     *
     * @template T
     * @param T $class
     *
     * @return ValidatorGuardCore&T
     */
    function valguard($class): ValidatorGuardCore
    {
        return new ValidatorGuardCore($class);
    }
}