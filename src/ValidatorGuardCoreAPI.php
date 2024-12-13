<?php

namespace MoeMizrak\ValidatorGuardCore;

abstract class ValidatorGuardCoreAPI
{
    /**
     * ValidatorGuardCoreAPI constructor.
     *
     * @param object $_class
     */
    public function __construct(protected object $_class) {}
}