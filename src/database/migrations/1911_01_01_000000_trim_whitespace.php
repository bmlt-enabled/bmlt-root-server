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
        DB::table('comdef_meetings_main')
            ->whereNotNull('worldid_mixed')
            ->update(['worldid_mixed' => DB::raw('TRIM(worldid_mixed)')]);
        DB::table('comdef_meetings_main')
            ->whereNotNull('email_contact')
            ->update(['email_contact' => DB::raw('TRIM(email_contact)')]);

        DB::table('comdef_meetings_data')
            ->whereNotNull('data_string')
            ->update(['data_string' => DB::raw('TRIM(data_string)')]);

        DB::table('comdef_meetings_longdata')
            ->whereNotNull('data_blob')
            ->update(['data_blob' => DB::raw('TRIM(data_blob)')]);

        DB::table('comdef_service_bodies')
            ->whereNotNull('name_string')
            ->update(['name_string' => DB::raw('TRIM(name_string)')]);
        DB::table('comdef_service_bodies')
            ->whereNotNull('description_string')
            ->update(['description_string' => DB::raw('TRIM(description_string)')]);
        DB::table('comdef_service_bodies')
            ->whereNotNull('worldid_mixed')
            ->update(['worldid_mixed' => DB::raw('TRIM(worldid_mixed)')]);
        DB::table('comdef_service_bodies')
            ->whereNotNull('kml_file_uri_string')
            ->update(['kml_file_uri_string' => DB::raw('TRIM(kml_file_uri_string)')]);
        DB::table('comdef_service_bodies')
            ->whereNotNull('uri_string')
            ->update(['uri_string' => DB::raw('TRIM(uri_string)')]);
        DB::table('comdef_service_bodies')
            ->whereNotNull('sb_meeting_email')
            ->update(['sb_meeting_email' => DB::raw('TRIM(sb_meeting_email)')]);

        DB::table('comdef_formats')
            ->whereNotNull('key_string')
            ->update(['key_string' => DB::raw('TRIM(key_string)')]);
        DB::table('comdef_formats')
            ->whereNotNull('worldid_mixed')
            ->update(['worldid_mixed' => DB::raw('TRIM(worldid_mixed)')]);
        DB::table('comdef_formats')
            ->whereNotNull('name_string')
            ->update(['name_string' => DB::raw('TRIM(name_string)')]);
        DB::table('comdef_formats')
            ->whereNotNull('description_string')
            ->update(['description_string' => DB::raw('TRIM(description_string)')]);
        DB::table('comdef_formats')
            ->whereNotNull('format_type_enum')
            ->update(['format_type_enum' => DB::raw('TRIM(format_type_enum)')]);
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
