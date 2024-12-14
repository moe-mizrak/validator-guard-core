<?php

use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\Callback;
use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\Comparison;
use MoeMizrak\ValidatorGuardCore\Tests\src\Attributes\NonPastDate;
use MoeMizrak\ValidatorGuardCore\Tests\src\Services\ExampleForBindingService;
use MoeMizrak\ValidatorGuardCore\Tests\src\Services\ExampleService;

return [
    /**
     * Here add the attributes that are used for Validation Guard
     */
    'attributes' => [
        'before' => [
            Callback::class,
            NonPastDate::class,
        ],
        'after' => [
            Comparison::class,
        ]
    ],
    /**
     * Here we add all classes that we use attributes validation in order to bind them to ValidatorGuardCore in Service Provider.
     * Basically whenever these classes are resolved by container, we initiate ValidatorGuardCore to mimic them as a wrapper and handle validation.
     */
    'class_list' => [
        ExampleService::class,
        ExampleForBindingService::class
    ]
];