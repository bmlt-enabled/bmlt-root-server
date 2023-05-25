<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 'MEETING_FORMAT';
    public const TYPE_LOCATION_CODE = 'LOCATION';
    public const TYPE_COMMON_NEEDS = 'COMMON_NEEDS_OR_RESTRICTION';
    public const TYPE_OPEN_CLOSED = 'OPEN_OR_CLOSED';
    public const TYPE_LANGUAGE = 'LANGUAGE';
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('comdef_format_types')) {
        } else {
            Schema::create('comdef_format_types', function (Blueprint $table) {
                $table->string('key_string', 10);
                $table->string('api_key', 256);
                $table->index('key_string');
                $table->index('api_key');
            });
            if (!legacy_config('aggregator_mode_enabled')) {
                // aggregator mode does not need any stock data
                DB::table('comdef_format_types')->insert([
                    ['key_string' => 'FC1', 'api_key' => 'MEETING_FORMAT'],
                    ['key_string' => 'FC2', 'api_key' =>  'LOCATION'],
                    ['key_string' => 'FC3', 'api_key' => 'COMMON_NEEDS_OR_RESTRICTION'],
                    ['key_string' => 'O', 'api_key' => 'OPEN_OR_CLOSED'],
                    ['key_string' => 'LANG', 'api_key' => 'LANGUAGE'],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
