<?php

use MoeMizrak\ValidatorGuardCore\ValidatorGuardCore;

if (! function_exists('valguard')) {
    function valguard(object $class): ValidatorGuardCore
    {
        return new ValidatorGuardCore($class);
    }
}
