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
    ],

    /**
     * Enable throwing exceptions in case of validation failure
     */
    'throw_exceptions' => env('VALIDATOR_GUARD_THROW_EXCEPTIONS', true),

    /**
     * Enable logging exceptions in case of validation failure
     */
    'log_exceptions' => env('VALIDATOR_GUARD_LOG_EXCEPTIONS', false),

    /**
     * Set an option for default channel for logging so that it can be configured when needed.
     */
    'log_channel' => env('VALIDATOR_GUARD_LOG_CHANNEL', 'stack'),
];