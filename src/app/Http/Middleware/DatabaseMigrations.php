<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\LegacyDbVersion;
use App\Models\Migration;

class DatabaseMigrations
{
    private ?bool $_allLegacyMigrationsHaveRun = null;

    public function handle(Request $request, Closure $next)
    {
        if ($this->migrationsShouldRun()) {
            $lockName = 'bmlt-db-migrations';
            try {
                DB::select("SELECT GET_LOCK('$lockName', 600)");
                Artisan::call('migrate', ['--force' => true]);
            } finally {
                DB::statement("SELECT RELEASE_LOCK('$lockName')");
            }
        } else if (!$this->allLegacyMigrationsHaveRun()) {
            // We need to at least be sure the sessions table exists
            $lockName = 'bmlt-db-migrations';
            try {
                DB::select("SELECT GET_LOCK('$lockName', 600)");
                Artisan::call('migrate', [
                    '--path' => '/database/migrations/1900_01_01_000000_create_sessions_table.php',
                    '--force' => true,
                ]);
            } finally {
                DB::statement("SELECT RELEASE_LOCK('$lockName')");
            }
        }

        return $next($request);
    }

    private function migrationsShouldRun(): bool
    {
        if (!Schema::hasTable('comdef_service_bodies')) {
            // There's no legacy schema at all, so we'll just create it
            return true;
        }

        if (!$this->allLegacyMigrationsHaveRun()) {
            // If the legacy migrations haven't run, we need to let them run before we run ours
            return false;
        }

        if (!$this->allEloquentMigrationsHaveRun()) {
            return true;
        }

        return false;
    }

    private function allLegacyMigrationsHaveRun(): bool
    {
        if (!is_null($this->_allLegacyMigrationsHaveRun)) {
            return $this->_allLegacyMigrationsHaveRun;
        }

        if (!Schema::hasTable('comdef_db_version')) {
            $this->_allLegacyMigrationsHaveRun = false;
            return false;
        }

        $version = LegacyDbVersion::get('version')->first();
        if (!$version || $version->version != 21) {
            $this->_allLegacyMigrationsHaveRun = false;
            return false;
        }

        $this->_allLegacyMigrationsHaveRun = true;
        return true;
    }

    private function allEloquentMigrationsHaveRun(): bool
    {
        if (!Schema::hasTable('migrations')) {
            return false;
        }

        if (!Migration::where('migration', '2022_08_30_235741_create_initial_schema')->exists()) {
            return false;
        }

        return true;
    }
}
