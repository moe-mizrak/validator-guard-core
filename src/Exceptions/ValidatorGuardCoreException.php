<?php

namespace MoeMizrak\ValidatorGuardCore\Exceptions;

use Exception;

/**
 * Custom exception for validator guard related exceptions
 *
 * @class ValidatorGuardCoreException
 */
class ValidatorGuardCoreException extends Exception
{
    public function __construct(string $message = 'Attribute validation failed !')
    {
        parent::__construct($message);
    }
}