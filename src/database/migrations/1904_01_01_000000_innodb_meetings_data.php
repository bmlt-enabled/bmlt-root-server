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
        $prefix = config('database.connections.mysql.prefix');
        DB::statement(DB::raw('ALTER TABLE ' . $prefix . 'comdef_meetings_data ENGINE = InnoDB;'));
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
