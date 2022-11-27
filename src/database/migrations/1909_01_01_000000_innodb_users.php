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
        $prefix = DB::connection()->getTablePrefix();
        DB::statement(DB::raw('ALTER TABLE ' . $prefix . 'comdef_users ENGINE = InnoDB;'));
        DB::statement(DB::raw('ALTER TABLE ' . $prefix . 'comdef_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'));
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
