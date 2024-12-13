<?php

namespace MoeMizrak\ValidatorGuardCore\Tests;

use MoeMizrak\ValidatorGuardCore\ValidatorGuardCoreServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param $app
     *
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            ValidatorGuardCoreServiceProvider::class,
        ];
    }
}