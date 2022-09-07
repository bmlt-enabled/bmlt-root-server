<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Migration;

class DatabaseMigrations
{
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
        }
        return $next($request);
    }

    private function migrationsShouldRun(): bool
    {
        if (!Schema::hasTable('migrations')) {
            return true;
        }

        if (!Migration::where('migration', '2022_08_30_235741_create_initial_schema')->exists()) {
            return true;
        }

        return false;
    }
}
