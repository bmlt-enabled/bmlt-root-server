<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Repositories\ServiceBodyRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ServiceBodyRepositoryInterface::class, ServiceBodyRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
