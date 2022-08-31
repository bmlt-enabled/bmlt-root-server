<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('comdef_db_version')) {
            if (!Schema::hasColumn('comdef_db_version', 'id')) {
                Schema::table('comdef_db_version', function(Blueprint $table) {
                   $table->increments('id')->first();
                });
            }
        } else {
            Schema::create('comdef_db_version', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('version');
            });
            DB::table('comdef_db_version')->insert(['version' => 21]);
        }

        if (Schema::hasTable('comdef_meetings_data')) {
            if (!Schema::hasColumn('comdef_meetings_data', 'id')) {
                Schema::table('comdef_meetings_data', function(Blueprint $table) {
                    $table->bigIncrements('id')->first();
                });
            }
        } else {
            Schema::create('comdef_meetings_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('meetingid_bigint');
                $table->string('key', 32);
                $table->string('field_prompt', 255)->nullable();
                $table->string('lang_enum', 7)->nullable();
                $table->integer('visibility')->nullable();
                $table->string('data_string', 255)->nullable();
                $table->bigInteger('data_bigint')->nullable();
                $table->double('data_double')->nullable();
                $table->index('data_bigint', 'data_bigint');
                $table->index('data_double', 'data_double');
                $table->index('meetingid_bigint', 'meetingid_bigint');
                $table->index('lang_enum', 'lang_enum');
                $table->index('key', 'key');
                $table->index('visibility', 'visibility');
            });
            DB::table('comdef_meetings_data')->insert([
                ['meetingid_bigint' => 0, 'key' => 'meeting_name', 'field_prompt' => 'Meeting Name', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Meeting Name', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_text', 'field_prompt' => 'Location Name', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Location Name', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_info', 'field_prompt' => 'Additional Location Information', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Additional Location Information', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_street', 'field_prompt' => 'Street Address', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Street Address', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_city_subsection', 'field_prompt' => 'Borough', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Borough', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_neighborhood', 'field_prompt' => 'Neighborhood', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Neighborhood', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_municipality', 'field_prompt' => 'Town', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Town', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_sub_province', 'field_prompt' => 'County', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'County', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_province', 'field_prompt' => 'State', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'State', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_postal_code_1', 'field_prompt' => 'Zip Code', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => null, 'data_bigint' => 0, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'location_nation', 'field_prompt' => 'Nation', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Nation', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'comments', 'field_prompt' => 'Comments', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Comments', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'train_lines', 'field_prompt' => 'Train Lines', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => null, 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'bus_lines', 'field_prompt' => 'Bus Lines', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => null, 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'contact_phone_2', 'field_prompt' => 'Contact 2 Phone', 'lang_enum' => 'en', 'visibility' => 1, 'data_string' => 'Contact 2 Phone', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'contact_email_2', 'field_prompt' => 'Contact 2 Email', 'lang_enum' => 'en', 'visibility' => 1, 'data_string' => 'Contact 2 Email', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'contact_name_2', 'field_prompt' => 'Contact 2 Name', 'lang_enum' => 'en', 'visibility' => 1, 'data_string' => 'Contact 2 Name', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'contact_phone_1', 'field_prompt' => 'Contact 1 Phone', 'lang_enum' => 'en', 'visibility' => 1, 'data_string' => 'Contact 1 Phone', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'contact_email_1', 'field_prompt' => 'Contact 1 Email', 'lang_enum' => 'en', 'visibility' => 1, 'data_string' => 'Contact 1 Email', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'contact_name_1', 'field_prompt' => 'Contact 1 Name', 'lang_enum' => 'en', 'visibility' => 1, 'data_string' => 'Contact 1 Name', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'phone_meeting_number', 'field_prompt' => 'Phone Meeting Dial-in Number', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Phone Meeting Dial-in Number', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'virtual_meeting_link', 'field_prompt' => 'Virtual Meeting Link', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Virtual Meeting Link', 'data_bigint' => null, 'data_double' => null],
                ['meetingid_bigint' => 0, 'key' => 'virtual_meeting_additional_info', 'field_prompt' => 'Virtual Meeting Additional Info', 'lang_enum' => 'en', 'visibility' => 0, 'data_string' => 'Virtual Meeting Additional Info', 'data_bigint' => null, 'data_double' => null]
            ]);
        }

        if (Schema::hasTable('comdef_meetings_longdata')) {
            if (!Schema::hasColumn('comdef_meetings_longdata', 'id')) {
                Schema::table('comdef_meetings_longdata', function(Blueprint $table) {
                    $table->bigIncrements('id')->first();
                });
            }
        } else {
            Schema::create('comdef_meetings_longdata', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('meetingid_bigint');
                $table->string('key', 32);
                $table->string('field_prompt', 255)->nullable();
                $table->string('lang_enum', 7)->nullable();
                $table->integer('visibility')->nullable();
                $table->text('data_longtext')->nullable();
                $table->binary('data_blob')->nullable();
                $table->index('meetingid_bigint', 'meetingid_bigint');
                $table->index('lang_enum', 'lang_enum');
                $table->index('field_prompt', 'field_prompt');
                $table->index('key', 'key');
                $table->index('visibility', 'visibility');
            });
        }

        if (Schema::hasTable('comdef_formats')) {
            if (!Schema::hasColumn('comdef_formats', 'id')) {
                Schema::table('comdef_formats', function(Blueprint $table) {
                    $table->bigIncrements('id')->first();
                });
            }
        } else {
            Schema::create('comdef_formats', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('shared_id_bigint');
                $table->string('key_string', 255)->nullable();
                $table->binary('icon_blob')->nullable();
                $table->string('worldid_mixed', 255)->nullable();
                $table->string('lang_enum', 7)->default('en');
                $table->string('name_string', 255)->nullable();
                $table->text('description_string')->nullable();
                $table->string('format_type_enum', 7)->default('FC1')->nullable();
                $table->index('shared_id_bigint', 'shared_id_bigint');
                $table->index('worldid_mixed', 'worldid_mixed');
                $table->index('format_type_enum', 'format_type_enum');
                $table->index('lang_enum', 'lang_enum');
                $table->index('key_string', 'key_string');
            });
        }

        if (Schema::hasTable('comdef_service_bodies')) {
            return;
        }

        Schema::create('comdef_meetings_main', function (Blueprint $table) {
            $table->bigIncrements('id_bigint');
            $table->string('worldid_mixed', 255)->nullable();
            $table->bigInteger('shared_group_id_bigint')->nullable();
            $table->unsignedBigInteger('service_body_bigint');
            $table->unsignedTinyInteger('weekday_tinyint')->nullable();
            $table->unsignedTinyInteger('venue_type')->nullable();
            $table->time('start_time')->nullable();
            $table->time('duration_time')->nullable();
            $table->string('time_zone', 40)->nullable();
            $table->string('formats', 255)->nullable();
            $table->string('lang_enum', 7)->nullable();
            $table->double('longitude')->nullable();
            $table->double('latitude')->nullable();
            $table->tinyInteger('published')->default(0);
            $table->string('email_contact', 255)->nullable();
            $table->index('weekday_tinyint', 'weekday_tinyint');
            $table->index('venue_type', 'venue_type');
            $table->index('service_body_bigint', 'service_body_bigint');
            $table->index('start_time', 'start_time');
            $table->index('duration_time', 'duration_time');
            $table->index('time_zone', 'time_zone');
            $table->index('formats', 'formats');
            $table->index('lang_enum', 'lang_enum');
            $table->index('worldid_mixed', 'worldid_mixed');
            $table->index('shared_group_id_bigint', 'shared_group_id_bigint');
            $table->index('longitude', 'longitude');
            $table->index('latitude', 'latitude');
            $table->index('published', 'published');
            $table->index('email_contact', 'email_contact');
        });

        Schema::create('comdef_service_bodies', function (Blueprint $table) {
            $table->bigIncrements('id_bigint');
            $table->string('name_string', 255);
            $table->text('description_string');
            $table->string('lang_enum', 7)->default('en');
            $table->string('worldid_mixed', 255)->nullable();
            $table->string('kml_file_uri_string', 255)->nullable();
            $table->unsignedBigInteger('principal_user_bigint')->nullable();
            $table->string('editors_string', 255)->nullable();
            $table->string('uri_string', 255)->nullable();
            $table->string('sb_type', 32)->nullable();
            $table->unsignedBigInteger('sb_owner')->nullable();
            $table->unsignedBigInteger('sb_owner_2')->nullable();
            $table->string('sb_meeting_email', 255);
            $table->index('worldid_mixed', 'worldid_mixed');
            $table->index('kml_file_uri_string', 'kml_file_uri_string');
            $table->index('principal_user_bigint', 'principal_user_bigint');
            $table->index('editors_string', 'editors_string');
            $table->index('lang_enum', 'lang_enum');
            $table->index('uri_string', 'uri_string');
            $table->index('sb_type', 'sb_type');
            $table->index('sb_owner', 'sb_owner');
            $table->index('sb_owner_2', 'sb_owner_2');
            $table->index('sb_meeting_email', 'sb_meeting_email');
        });

        Schema::create('comdef_users', function (Blueprint $table) {
            $table->bigIncrements('id_bigint');
            $table->unsignedTinyInteger('user_level_tinyint')->default(0);
            $table->string('name_string', 255);
            $table->text('description_string');
            $table->string('email_address_string', 255);
            $table->string('login_string', 255);
            $table->string('password_string', 255);
            $table->dateTime('last_access_datetime')->default('1970-01-01 00:00:00');
            $table->string('lang_enum', 7)->default('en');
            $table->bigInteger('owner_id_bigint')->default(-1);
            $table->unique('login_string', 'login_string');
            $table->index('user_level_tinyint', 'user_level_tinyint');
            $table->index('email_address_string', 'email_address_string');
            $table->index('last_access_datetime', 'last_access_datetime');
            $table->index('lang_enum', 'lang_enum');
            $table->index('owner_id_bigint', 'owner_id_bigint');
        });

        Schema::create('comdef_changes', function (Blueprint $table) {
            $table->bigIncrements('id_bigint');
            $table->unsignedBigInteger('user_id_bigint');
            $table->unsignedBigInteger('service_body_id_bigint');
            $table->string('lang_enum', 7);
            $table->timestamp('change_date')->useCurrent()->useCurrentOnUpdate();
            $table->string('object_class_string', 64);
            $table->string('change_name_string', 255)->nullable();
            $table->text('change_description_text')->nullable();
            $table->unsignedBigInteger('before_id_bigint')->nullable();
            $table->string('before_lang_enum', 7)->nullable();
            $table->unsignedBigInteger('after_id_bigint')->nullable();
            $table->string('after_lang_enum', 7)->nullable();
            $table->string('change_type_enum', 32);
            $table->binary('before_object')->nullable();
            $table->binary('after_object')->nullable();
            $table->index('user_id_bigint', 'user_id_bigint');
            $table->index('service_body_id_bigint', 'service_body_id_bigint');
            $table->index('lang_enum', 'lang_enum');
            $table->index('change_type_enum', 'change_type_enum');
            $table->index('change_date', 'change_date');
            $table->index('before_id_bigint', 'before_id_bigint');
            $table->index('after_id_bigint', 'after_id_bigint');
            $table->index('before_lang_enum', 'before_lang_enum');
            $table->index('after_lang_enum', 'after_lang_enum');
            $table->index('object_class_string', 'object_class_string');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comdef_db_version');
        Schema::dropIfExists('comdef_meetings_data');
        Schema::dropIfExists('comdef_meetings_longdata');
        Schema::dropIfExists('comdef_meetings_main');
        Schema::dropIfExists('comdef_formats');
        Schema::dropIfExists('comdef_service_bodies');
        Schema::dropIfExists('comdef_users');
        Schema::dropIfExists('comdef_changes');
    }
};
