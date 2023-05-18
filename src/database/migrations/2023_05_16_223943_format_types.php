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
                $table->text('description_string')->nullable();
                $table->text('ui_enum')->nullable();
                $table->index('key_string', 'key_string');
            });
            if (!legacy_config('aggregator_mode_enabled')) {
                // aggregator mode does not need any stock data
                DB::table('comdef_format_types')->insert([
                    ['key_string' => 'FC1', 'description_string' => 'MEETING_FORMAT', 'ui_enum' => 'CHECKBOX'],
                    ['key_string' => 'FC2', 'description_string' =>  'LOCATION', 'ui_enum' => 'CHECKBOX'],
                    ['key_string' => 'FC3', 'description_string' => 'COMMON_NEEDS_OR_RESTRICTION', 'ui_enum' => 'CHECKBOX'],
                    ['key_string' => 'O', 'description_string' => 'OPEN_OR_CLOSED', 'ui_enum' => 'RADIO'],
                    ['key_string' => 'LANG', 'description_string' => 'LANGUAGE', 'ui_enum' => 'CHECKBOX'],
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
