<?php

namespace MoeMizrak\ValidatorGuardCore\Tests;

use MoeMizrak\ValidatorGuardCore\ValidatorGuardCoreServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            ValidatorGuardCoreServiceProvider::class,
        ];
    }
}
