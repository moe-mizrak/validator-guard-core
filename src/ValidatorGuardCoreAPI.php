<?php

namespace MoeMizrak\ValidatorGuardCore;

readonly abstract class ValidatorGuardCoreAPI
{
    /*
     *  After method execution key
     */
    protected const _AFTER = 'after';

    /*
     * Name of the handle method defined in attributes
     */
    protected const _HANDLE_METHOD_NAME = 'handle';

    /**
     * ValidatorGuardCoreAPI constructor.
     *
     * @param object $_class
     */
    public function __construct(protected object $_class) {}
}