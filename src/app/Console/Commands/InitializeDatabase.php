<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InitializeDatabase extends Command
{
    protected $signature = 'aggregator:InitializeDatabase';

    protected $description = 'Initialize database';

    public function handle()
    {
        if (!legacy_config('aggregator_mode_enabled')) {
            $this->error('aggregator mode is not enabled');
            return;
        }

        $lockName = 'bmlt-db-migrations';
        try {
            ini_set('max_execution_time', '600');
            DB::select("SELECT GET_LOCK('$lockName', 600)");
            Artisan::call('migrate', ['--force' => true]);
        } finally {
            DB::statement("SELECT RELEASE_LOCK('$lockName')");
        }
    }
}
