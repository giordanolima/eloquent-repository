<?php

namespace GiordanoLima\EloquentRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->handleConfigs();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Register the configuration.
     */
    private function handleConfigs()
    {
        $configPath = __DIR__.'/../config/repository.php';
        $this->publishes([$configPath => config_path('repository.php')]);
        $this->mergeConfigFrom($configPath, 'repository');
    }
}
