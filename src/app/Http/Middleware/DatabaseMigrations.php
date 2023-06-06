<?php

namespace App\Http\Middleware;

use App\Interfaces\MigrationRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseMigrations
{
    private MigrationRepositoryInterface $migrationRepository;

    public function __construct(MigrationRepositoryInterface $migrationRepository)
    {
        $this->migrationRepository = $migrationRepository;
    }

    public function handle(Request $request, Closure $next)
    {
        if (config('app.env') != 'testing') {
            // When under PHPUnit, the 'database.connections.mysql.database' key will always be null,
            // so we just skip the check. Running different codepaths under test is an anti-pattern,
            // and is pretty much always a bad idea. In this case, the block below should only get hit
            // when the config file is missing and the installer is running. There is probably a better
            // way, but I haven't thought of one yet.
            if (!config('database.connections.mysql.database')) {
                // This prevents a database access before the install wizard has written a config file
                return $next($request);
            }
        }

        if ($this->migrationsShouldRun()) {
            $lockName = 'bmlt-db-migrations';
            try {
                ini_set('max_execution_time', '600');
                DB::select("SELECT GET_LOCK('$lockName', 600)");
                Artisan::call('migrate', ['--force' => true]);
            } finally {
                DB::statement("SELECT RELEASE_LOCK('$lockName')");
            }
        }

        return $next($request);
    }

    private function migrationsShouldRun(): bool
    {
        if (!Schema::hasTable('migrations')) {
            return true;
        }

        if (!$this->migrationRepository->migrationExists('2023_05_16_223943_format_types')) {
            return true;
        }

        return false;
    }
}
