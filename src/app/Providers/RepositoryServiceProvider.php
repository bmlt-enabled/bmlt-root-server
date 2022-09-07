<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Repositories\FormatRepository;
use App\Repositories\MeetingRepository;
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
        $this->app->bind(MeetingRepositoryInterface::class, MeetingRepository::class);
        $this->app->bind(FormatRepositoryInterface::class, FormatRepository::class);
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
