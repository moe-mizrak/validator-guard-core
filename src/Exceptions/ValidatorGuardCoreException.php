<?php

namespace MoeMizrak\ValidatorGuardCore\Exceptions;

use Exception;

class ValidatorGuardCoreException extends Exception
{
    public function __construct(string $message = 'Attribute validation failed !')
    {
        parent::__construct($message);
    }
}