<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!legacy_config('aggregator_mode_enabled')) {
            DB::table('comdef_meetings_main')
                ->whereNull('lang_enum')
                ->update(['lang_enum' => config('app.locale')]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
