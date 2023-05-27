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
        Schema::create('comdef_format_types', function (Blueprint $table) {
            $table->string('key_string', 10)->unique();
            $table->string('api_enum', 256)->unique();
            $table->integer('position_int');
            $table->index('key_string');
            $table->index('api_enum');
        });
        if (!legacy_config('aggregator_mode_enabled')) {
            // aggregator mode does not need any stock data
            DB::table('comdef_format_types')->insert([
                ['key_string' => 'FC1', 'api_enum' => 'MEETING_FORMAT', 'position_int' => '1'],
                ['key_string' => 'FC2', 'api_enum' =>  'LOCATION', 'position_int' => '2'],
                ['key_string' => 'FC3', 'api_enum' => 'COMMON_NEEDS_OR_RESTRICTION', 'position_int' => '3'],
                ['key_string' => 'O', 'api_enum' => 'OPEN_OR_CLOSED', 'position_int' => '4'],
                ['key_string' => 'LANG', 'api_enum' => 'LANGUAGE', 'position_int' => '5'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comdef_format_types');
    }
};
