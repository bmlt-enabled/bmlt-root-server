<?php

namespace App\Providers;

use App\Interfaces\ChangeRepositoryInterface;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\MigrationRepositoryInterface;
use App\Interfaces\RootServerRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\RootServer;
use App\Repositories\ChangeRepository;
use App\Repositories\FormatRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\MigrationRepository;
use App\Repositories\RootServerRepository;
use App\Repositories\ServiceBodyRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ChangeRepositoryInterface::class, ChangeRepository::class);
        $this->app->bind(FormatRepositoryInterface::class, FormatRepository::class);
        $this->app->bind(MeetingRepositoryInterface::class, MeetingRepository::class);
        $this->app->bind(MigrationRepositoryInterface::class, MigrationRepository::class);
        $this->app->bind(RootServerRepositoryInterface::class, RootServerRepository::class);
        $this->app->bind(ServiceBodyRepositoryInterface::class, ServiceBodyRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
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
