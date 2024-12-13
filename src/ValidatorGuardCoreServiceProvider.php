<?php

namespace MoeMizrak\ValidatorGuardCore;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for ValidatorGuardCore
 */
class ValidatorGuardCoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->configure();

        $this->bindClasses();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['validator-guard-core'];
    }

    /**
     * Setup the configuration.
     *
     * @return void
     */
    protected function configure(): void
    {
        // Get the config path based on environment
        $configPath = app()->environment('testing')
            ? __DIR__ . '/../tests/config/validator-guard-core.php'
            : config_path('validator-guard-core.php');

        // Merge configuration
        if (file_exists($configPath)) {
            $this->mergeConfigFrom(
                $configPath, 'validator-guard-core'
            );
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/validator-guard-core.php' => config_path('validator-guard-core.php'),
            ], 'validator-guard-core');
        }
    }

    /**
     * Loop through class_list in config and bind them to ValidatorGuardCore
     *
     * @return void
     */
    private function bindClasses(): void
    {
        // List of classes in order to bind to ValidatorGuardCore
        $classList = config('validator-guard-core.class_list');

        // Loop through classes and bind them to ValidatorGuardCore
        foreach ($classList as $class) {
            $this->app->bind($class, function () use ($class) {
                return new ValidatorGuardCore(
                    new $class
                );
            });
        }
    }
}