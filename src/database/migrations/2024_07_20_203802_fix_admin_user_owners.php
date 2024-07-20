<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!legacy_config('aggregator_mode_enabled')) {
            $adminUserIds = DB::table('comdef_users')->where('user_level_tinyint', 1)->pluck('id_bigint');
            DB::table('comdef_users')->whereIn('owner_id_bigint', $adminUserIds)->update(['owner_id_bigint' => -1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
