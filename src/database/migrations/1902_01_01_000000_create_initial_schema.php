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
                Schema::table('comdef_db_version', function (Blueprint $table) {
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
                Schema::table('comdef_meetings_data', function (Blueprint $table) {
                    $table->bigIncrements('id')->first();
                    $table->fullText('data_string');
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
                $table->fullText('data_string');
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
                Schema::table('comdef_meetings_longdata', function (Blueprint $table) {
                    $table->bigIncrements('id')->first();
                    $table->text('data_blob')->nullable()->change();
                    $table->dropColumn('data_longtext');
                    $table->fullText('data_blob');
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
                $table->text('data_blob')->nullable();
                $table->index('meetingid_bigint', 'meetingid_bigint');
                $table->index('lang_enum', 'lang_enum');
                $table->index('field_prompt', 'field_prompt');
                $table->index('key', 'key');
                $table->index('visibility', 'visibility');
                $table->fullText('data_blob');
            });
        }

        if (Schema::hasTable('comdef_formats')) {
            if (!Schema::hasColumn('comdef_formats', 'id')) {
                Schema::table('comdef_formats', function (Blueprint $table) {
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
            DB::table('comdef_formats')->insert([
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'de', 'name_string' => 'Beginners', 'description_string' => 'This meeting is focused on the needs of new members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'de', 'name_string' => 'Bi-Lingual', 'description_string' => 'This Meeting can be attended by speakers of English and another language.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'BT', 'worldid_mixed' => 'BT', 'lang_enum' => 'de', 'name_string' => 'Basic Text', 'description_string' => 'This meeting is focused on discussion of the Basic Text of Narcotics Anonymous.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'de', 'name_string' => 'Closed', 'description_string' => 'This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => 'CH', 'lang_enum' => 'de', 'name_string' => 'Closed Holidays', 'description_string' => 'This meeting gathers in a facility that is usually closed on holidays.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'de', 'name_string' => 'Candlelight', 'description_string' => 'This meeting is held by candlelight.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'de', 'name_string' => 'Children under Supervision', 'description_string' => 'Well-behaved, supervised children are welcome.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'de', 'name_string' => 'Discussion', 'description_string' => 'This meeting invites participation by all attendees.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'de', 'name_string' => 'Español', 'description_string' => 'This meeting is conducted in Spanish.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'de', 'name_string' => 'Gay/Lesbian/Transgender', 'description_string' => 'This meeting is focused on the needs of gay, lesbian and transgender members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Illness', 'description_string' => 'This meeting is focused on the needs of NA members with chronic illness.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'de', 'name_string' => 'Informational Pamphlet', 'description_string' => 'This meeting is focused on discussion of one or more Informational Pamphlets.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'de', 'name_string' => 'It Works -How and Why', 'description_string' => 'This meeting is focused on discussion of the It Works -How and Why text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'de', 'name_string' => 'Just for Today', 'description_string' => 'This meeting is focused on discussion of the Just For Today text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'de', 'name_string' => 'Men', 'description_string' => 'This meeting is meant to be attended by men only.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => 'NC', 'lang_enum' => 'de', 'name_string' => 'No Children', 'description_string' => 'Please do not bring children to this meeting.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'de', 'name_string' => 'Open', 'description_string' => 'This meeting is open to addicts and non-addicts alike. All are welcome.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Pitch', 'description_string' => 'This meeting has a format that consists of each person who shares picking the next person.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'de', 'name_string' => 'Rotating Format', 'description_string' => 'This meeting has a format that changes for each meeting.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Round Robin', 'description_string' => 'This meeting has a fixed sharing order (usually a circle.)', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Security Cameras', 'description_string' => 'This meeting is held in a facility that has security cameras.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'de', 'name_string' => 'Speaker/Discussion', 'description_string' => 'This meeting is lead by a speaker, then opened for participation by attendees.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'de', 'name_string' => 'Step Working Guide', 'description_string' => 'This meeting is focused on discussion of the Step Working Guide text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'ASL', 'description_string' => 'This meeting provides an American Sign Language (ASL) interpreter for the deaf.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'de', 'name_string' => 'Speaker Only', 'description_string' => 'This meeting is a speaker-only meeting. Other attendees do not participate in the discussion.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'de', 'name_string' => 'Step', 'description_string' => 'This meeting is focused on discussion of the Twelve Steps of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Timer', 'description_string' => 'This meeting has sharing time limited by a timer.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'de', 'name_string' => 'Topic', 'description_string' => 'This meeting is based upon a topic chosen by a speaker or by group conscience.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'de', 'name_string' => 'Tradition', 'description_string' => 'This meeting is focused on discussion of the Twelve Traditions of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'de', 'name_string' => 'Traditions Workshop', 'description_string' => 'This meeting engages in detailed discussion of one or more of the Twelve Traditions of N.A.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'de', 'name_string' => 'Women', 'description_string' => 'This meeting is meant to be attended by women only.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'de', 'name_string' => 'Wheelchair', 'description_string' => 'This meeting is wheelchair accessible.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'de', 'name_string' => 'Young People', 'description_string' => 'This meeting is focused on the needs of younger members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Open-Ended', 'description_string' => 'No fixed duration. The meeting continues until everyone present has had a chance to share.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'BK', 'worldid_mixed' => 'LIT', 'lang_enum' => 'de', 'name_string' => 'Book Study', 'description_string' => 'Approved N.A. Books', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NS', 'worldid_mixed' => 'NS', 'lang_enum' => 'de', 'name_string' => 'No Smoking', 'description_string' => 'Smoking is not allowed at this meeting.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Agnostic', 'description_string' => 'Intended for people with varying degrees of Faith.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'FD', 'worldid_mixed' => null, 'lang_enum' => 'de', 'name_string' => 'Five and Dime', 'description_string' => 'Discussion of the Fifth Step and the Tenth Step', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'AB', 'worldid_mixed' => 'QA', 'lang_enum' => 'de', 'name_string' => 'Ask-It-Basket', 'description_string' => 'A topic is chosen from suggestions placed into a basket.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'de', 'name_string' => 'Meditation', 'description_string' => 'This meeting encourages its participants to engage in quiet meditation.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'RA', 'worldid_mixed' => 'RA', 'lang_enum' => 'de', 'name_string' => 'Restricted Attendance', 'description_string' => 'This facility places restrictions on attendees.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'QA', 'worldid_mixed' => 'QA', 'lang_enum' => 'de', 'name_string' => 'Question and Answer', 'description_string' => 'Attendees may ask questions and expect answers from Group members.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'CW', 'worldid_mixed' => 'CW', 'lang_enum' => 'de', 'name_string' => 'Children Welcome', 'description_string' => 'Children are welcome at this meeting.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'CP', 'worldid_mixed' => 'CPT', 'lang_enum' => 'de', 'name_string' => 'Concepts', 'description_string' => 'This meeting is focused on discussion of the twelve concepts of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'FIN', 'worldid_mixed' => 'LANG', 'lang_enum' => 'de', 'name_string' => 'Finnish', 'description_string' => 'Finnish speaking meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => 'LANG', 'lang_enum' => 'de', 'name_string' => 'English speaking', 'description_string' => 'This Meeting can be attended by speakers of English.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => 'LANG', 'lang_enum' => 'de', 'name_string' => 'Persian', 'description_string' => 'Persian speaking meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 49, 'key_string' => 'L/R', 'worldid_mixed' => 'LANG', 'lang_enum' => 'de', 'name_string' => 'Lithuanian/Russian', 'description_string' => 'Lithuanian/Russian Speaking Meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 51, 'key_string' => 'LC', 'worldid_mixed' => 'LC', 'lang_enum' => 'de', 'name_string' => 'Living Clean', 'description_string' => 'This is a discussion of the NA book Living Clean -The Journey Continues.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 52, 'key_string' => 'GP', 'worldid_mixed' => 'GP', 'lang_enum' => 'de', 'name_string' => 'Guiding Principles', 'description_string' => 'This is a discussion of the NA book Guiding Principles - The Spirit of Our Traditions.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'de', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'de', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' => 'HYBR', 'lang_enum' => 'de', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'dk', 'name_string' => 'Beginners', 'description_string' => 'This meeting is focused on the needs of new members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'dk', 'name_string' => 'Bi-Lingual', 'description_string' => 'This Meeting can be attended by speakers of English and another language.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'BT', 'worldid_mixed' => 'BT', 'lang_enum' => 'dk', 'name_string' => 'Basic Text', 'description_string' => 'This meeting is focused on discussion of the Basic Text of Narcotics Anonymous.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'dk', 'name_string' => 'Closed', 'description_string' => 'This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => 'CH', 'lang_enum' => 'dk', 'name_string' => 'Closed Holidays', 'description_string' => 'This meeting gathers in a facility that is usually closed on holidays.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'dk', 'name_string' => 'Candlelight', 'description_string' => 'This meeting is held by candlelight.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'dk', 'name_string' => 'Children under Supervision', 'description_string' => 'Well-behaved, supervised children are welcome.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'dk', 'name_string' => 'Discussion', 'description_string' => 'This meeting invites participation by all attendees.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'dk', 'name_string' => 'Español', 'description_string' => 'This meeting is conducted in Spanish.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'dk', 'name_string' => 'Gay/Lesbian/Transgender', 'description_string' => 'This meeting is focused on the needs of gay, lesbian and transgender members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Illness', 'description_string' => 'This meeting is focused on the needs of NA members with chronic illness.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'dk', 'name_string' => 'Informational Pamphlet', 'description_string' => 'This meeting is focused on discussion of one or more Informational Pamphlets.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'dk', 'name_string' => 'It Works -How and Why', 'description_string' => 'This meeting is focused on discussion of the It Works -How and Why text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'dk', 'name_string' => 'Just for Today', 'description_string' => 'This meeting is focused on discussion of the Just For Today text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'dk', 'name_string' => 'Men', 'description_string' => 'This meeting is meant to be attended by men only.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => 'NC', 'lang_enum' => 'dk', 'name_string' => 'No Children', 'description_string' => 'Please do not bring children to this meeting.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'dk', 'name_string' => 'Open', 'description_string' => 'This meeting is open to addicts and non-addicts alike. All are welcome.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Pitch', 'description_string' => 'This meeting has a format that consists of each person who shares picking the next person.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'dk', 'name_string' => 'Rotating Format', 'description_string' => 'This meeting has a format that changes for each meeting.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Round Robin', 'description_string' => 'This meeting has a fixed sharing order (usually a circle.)', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Security Cameras', 'description_string' => 'This meeting is held in a facility that has security cameras.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'dk', 'name_string' => 'Speaker/Discussion', 'description_string' => 'This meeting is lead by a speaker, then opened for participation by attendees.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'dk', 'name_string' => 'Step Working Guide', 'description_string' => 'This meeting is focused on discussion of the Step Working Guide text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'ASL', 'description_string' => 'This meeting provides an American Sign Language (ASL) interpreter for the deaf.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'dk', 'name_string' => 'Speaker Only', 'description_string' => 'This meeting is a speaker-only meeting. Other attendees do not participate in the discussion.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'dk', 'name_string' => 'Step', 'description_string' => 'This meeting is focused on discussion of the Twelve Steps of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Timer', 'description_string' => 'This meeting has sharing time limited by a timer.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'dk', 'name_string' => 'Topic', 'description_string' => 'This meeting is based upon a topic chosen by a speaker or by group conscience.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'dk', 'name_string' => 'Tradition', 'description_string' => 'This meeting is focused on discussion of the Twelve Traditions of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'dk', 'name_string' => 'Traditions Workshop', 'description_string' => 'This meeting engages in detailed discussion of one or more of the Twelve Traditions of N.A.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'dk', 'name_string' => 'Women', 'description_string' => 'This meeting is meant to be attended by women only.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'dk', 'name_string' => 'Wheelchair', 'description_string' => 'This meeting is wheelchair accessible.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'dk', 'name_string' => 'Young People', 'description_string' => 'This meeting is focused on the needs of younger members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Open-Ended', 'description_string' => 'No fixed duration. The meeting continues until everyone present has had a chance to share.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'BK', 'worldid_mixed' => 'LIT', 'lang_enum' => 'dk', 'name_string' => 'Book Study', 'description_string' => 'Approved N.A. Books', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NS', 'worldid_mixed' => 'NS', 'lang_enum' => 'dk', 'name_string' => 'No Smoking', 'description_string' => 'Smoking is not allowed at this meeting.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Agnostic', 'description_string' => 'Intended for people with varying degrees of Faith.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'FD', 'worldid_mixed' => null, 'lang_enum' => 'dk', 'name_string' => 'Five and Dime', 'description_string' => 'Discussion of the Fifth Step and the Tenth Step', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'AB', 'worldid_mixed' => 'QA', 'lang_enum' => 'dk', 'name_string' => 'Ask-It-Basket', 'description_string' => 'A topic is chosen from suggestions placed into a basket.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'dk', 'name_string' => 'Meditation', 'description_string' => 'This meeting encourages its participants to engage in quiet meditation.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'RA', 'worldid_mixed' => 'RA', 'lang_enum' => 'dk', 'name_string' => 'Restricted Attendance', 'description_string' => 'This facility places restrictions on attendees.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'QA', 'worldid_mixed' => 'QA', 'lang_enum' => 'dk', 'name_string' => 'Question and Answer', 'description_string' => 'Attendees may ask questions and expect answers from Group members.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'CW', 'worldid_mixed' => 'CW', 'lang_enum' => 'dk', 'name_string' => 'Children Welcome', 'description_string' => 'Children are welcome at this meeting.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'CP', 'worldid_mixed' => 'CPT', 'lang_enum' => 'dk', 'name_string' => 'Concepts', 'description_string' => 'This meeting is focused on discussion of the twelve concepts of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'FIN', 'worldid_mixed' => 'LANG', 'lang_enum' => 'dk', 'name_string' => 'Finnish', 'description_string' => 'Finnish speaking meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => 'LANG', 'lang_enum' => 'dk', 'name_string' => 'English speaking', 'description_string' => 'This Meeting can be attended by speakers of English.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => 'LANG', 'lang_enum' => 'dk', 'name_string' => 'Persian', 'description_string' => 'Persian speaking meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 49, 'key_string' => 'L/R', 'worldid_mixed' => 'LANG', 'lang_enum' => 'dk', 'name_string' => 'Lithuanian/Russian', 'description_string' => 'Lithuanian/Russian Speaking Meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 51, 'key_string' => 'LC', 'worldid_mixed' => 'LC', 'lang_enum' => 'dk', 'name_string' => 'Living Clean', 'description_string' => 'This is a discussion of the NA book Living Clean -The Journey Continues.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 52, 'key_string' => 'GP', 'worldid_mixed' => 'GP', 'lang_enum' => 'dk', 'name_string' => 'Guiding Principles', 'description_string' => 'This is a discussion of the NA book Guiding Principles - The Spirit of Our Traditions.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'dk', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'dk', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' => 'HYBR', 'lang_enum' => 'dk', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'en', 'name_string' => 'Beginners', 'description_string' => 'This meeting is focused on the needs of new members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'en', 'name_string' => 'Bi-Lingual', 'description_string' => 'This Meeting can be attended by speakers of English and another language.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'BT', 'worldid_mixed' => 'BT', 'lang_enum' => 'en', 'name_string' => 'Basic Text', 'description_string' => 'This meeting is focused on discussion of the Basic Text of Narcotics Anonymous.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'en', 'name_string' => 'Closed', 'description_string' => 'This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => 'CH', 'lang_enum' => 'en', 'name_string' => 'Closed Holidays', 'description_string' => 'This meeting gathers in a facility that is usually closed on holidays.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'en', 'name_string' => 'Candlelight', 'description_string' => 'This meeting is held by candlelight.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'en', 'name_string' => 'Children under Supervision', 'description_string' => 'Well-behaved, supervised children are welcome.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'en', 'name_string' => 'Discussion', 'description_string' => 'This meeting invites participation by all attendees.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'en', 'name_string' => 'Español', 'description_string' => 'This meeting is conducted in Spanish.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'en', 'name_string' => 'Gay/Lesbian/Transgender', 'description_string' => 'This meeting is focused on the needs of gay, lesbian and transgender members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Illness', 'description_string' => 'This meeting is focused on the needs of NA members with chronic illness.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'en', 'name_string' => 'Informational Pamphlet', 'description_string' => 'This meeting is focused on discussion of one or more Informational Pamphlets.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'en', 'name_string' => 'It Works -How and Why', 'description_string' => 'This meeting is focused on discussion of the It Works -How and Why text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'en', 'name_string' => 'Just for Today', 'description_string' => 'This meeting is focused on discussion of the Just For Today text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'en', 'name_string' => 'Men', 'description_string' => 'This meeting is meant to be attended by men only.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => 'NC', 'lang_enum' => 'en', 'name_string' => 'No Children', 'description_string' => 'Please do not bring children to this meeting.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'en', 'name_string' => 'Open', 'description_string' => 'This meeting is open to addicts and non-addicts alike. All are welcome.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Pitch', 'description_string' => 'This meeting has a format that consists of each person who shares picking the next person.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'en', 'name_string' => 'Rotating Format', 'description_string' => 'This meeting has a format that changes for each meeting.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Round Robin', 'description_string' => 'This meeting has a fixed sharing order (usually a circle.)', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Security Cameras', 'description_string' => 'This meeting is held in a facility that has security cameras.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'en', 'name_string' => 'Speaker/Discussion', 'description_string' => 'This meeting is lead by a speaker, then opened for participation by attendees.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'en', 'name_string' => 'Step Working Guide', 'description_string' => 'This meeting is focused on discussion of the Step Working Guide text.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'ASL', 'description_string' => 'This meeting provides an American Sign Language (ASL) interpreter for the deaf.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'en', 'name_string' => 'Speaker Only', 'description_string' => 'This meeting is a speaker-only meeting. Other attendees do not participate in the discussion.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'en', 'name_string' => 'Step', 'description_string' => 'This meeting is focused on discussion of the Twelve Steps of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Timer', 'description_string' => 'This meeting has sharing time limited by a timer.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'en', 'name_string' => 'Topic', 'description_string' => 'This meeting is based upon a topic chosen by a speaker or by group conscience.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'en', 'name_string' => 'Tradition', 'description_string' => 'This meeting is focused on discussion of the Twelve Traditions of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'en', 'name_string' => 'Traditions Workshop', 'description_string' => 'This meeting engages in detailed discussion of one or more of the Twelve Traditions of N.A.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'en', 'name_string' => 'Women', 'description_string' => 'This meeting is meant to be attended by women only.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'en', 'name_string' => 'Wheelchair', 'description_string' => 'This meeting is wheelchair accessible.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'en', 'name_string' => 'Young People', 'description_string' => 'This meeting is focused on the needs of younger members of NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Open-Ended', 'description_string' => 'No fixed duration. The meeting continues until everyone present has had a chance to share.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'BK', 'worldid_mixed' => 'LIT', 'lang_enum' => 'en', 'name_string' => 'Book Study', 'description_string' => 'Approved N.A. Books', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NS', 'worldid_mixed' => 'NS', 'lang_enum' => 'en', 'name_string' => 'No Smoking', 'description_string' => 'Smoking is not allowed at this meeting.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Agnostic', 'description_string' => 'Intended for people with varying degrees of Faith.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'FD', 'worldid_mixed' => null, 'lang_enum' => 'en', 'name_string' => 'Five and Dime', 'description_string' => 'Discussion of the Fifth Step and the Tenth Step', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'AB', 'worldid_mixed' => 'QA', 'lang_enum' => 'en', 'name_string' => 'Ask-It-Basket', 'description_string' => 'A topic is chosen from suggestions placed into a basket.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'en', 'name_string' => 'Meditation', 'description_string' => 'This meeting encourages its participants to engage in quiet meditation.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'RA', 'worldid_mixed' => 'RA', 'lang_enum' => 'en', 'name_string' => 'Restricted Attendance', 'description_string' => 'This facility places restrictions on attendees.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'QA', 'worldid_mixed' => 'QA', 'lang_enum' => 'en', 'name_string' => 'Question and Answer', 'description_string' => 'Attendees may ask questions and expect answers from Group members.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'CW', 'worldid_mixed' => 'CW', 'lang_enum' => 'en', 'name_string' => 'Children Welcome', 'description_string' => 'Children are welcome at this meeting.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'CP', 'worldid_mixed' => 'CPT', 'lang_enum' => 'en', 'name_string' => 'Concepts', 'description_string' => 'This meeting is focused on discussion of the twelve concepts of NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'FIN', 'worldid_mixed' => 'LANG', 'lang_enum' => 'en', 'name_string' => 'Finnish', 'description_string' => 'Finnish speaking meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => 'LANG', 'lang_enum' => 'en', 'name_string' => 'English speaking', 'description_string' => 'This Meeting can be attended by speakers of English.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => 'LANG', 'lang_enum' => 'en', 'name_string' => 'Persian', 'description_string' => 'Persian speaking meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 49, 'key_string' => 'L/R', 'worldid_mixed' => 'LANG', 'lang_enum' => 'en', 'name_string' => 'Lithuanian/Russian', 'description_string' => 'Lithuanian/Russian Speaking Meeting', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 51, 'key_string' => 'LC', 'worldid_mixed' => 'LC', 'lang_enum' => 'en', 'name_string' => 'Living Clean', 'description_string' => 'This is a discussion of the NA book Living Clean -The Journey Continues.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 52, 'key_string' => 'GP', 'worldid_mixed' => 'GP', 'lang_enum' => 'en', 'name_string' => 'Guiding Principles', 'description_string' => 'This is a discussion of the NA book Guiding Principles - The Spirit of Our Traditions.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'en', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'en', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' => 'HYBR', 'lang_enum' => 'en', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'es', 'name_string' => 'Para el recién llegado', 'description_string' => 'Esta reunión se centra en las necesidades de los nuevos miembros de NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'es', 'name_string' => 'Bilingüe', 'description_string' => 'Esta reunión se pueden asistir personas de que hablen inglés y otro idioma.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'BT', 'worldid_mixed' => 'BT', 'lang_enum' => 'es', 'name_string' => 'Texto Básico', 'description_string' => 'Esta reunión se centra en la discusión del texto básico de Narcóticos Anónimos.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'es', 'name_string' => 'Cerrado', 'description_string' => 'Esta reunión está cerrada a los no adictos. Usted debe asistir solamente si cree que puede tener un problema con abuso de drogas.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'Cerrado en Días de fiesta', 'description_string' => 'Esta reunión tiene lugar en una localidad que esta generalmente cerrada los días de fiesta.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'es', 'name_string' => 'Luz de vela', 'description_string' => 'Esta reunión se celebra a luz de vela.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'es', 'name_string' => 'Niños bajo Supervisión', 'description_string' => 'Los niños de buen comportamiento y supervisados son bienvenidos.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'es', 'name_string' => 'Discusión', 'description_string' => 'Esta reunión invita la participación de todos los asistentes.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'es', 'name_string' => 'Gay/Lesbiana', 'description_string' => 'Esta reunión se centra en las necesidades de miembros gay y lesbianas de NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'Enfermedad', 'description_string' => 'Esta reunión se centra en las necesidades de los miembros de NA con enfermedades crónicas.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'es', 'name_string' => 'Folleto Informativo', 'description_string' => 'Esta reunión se centra en la discusión de unos o más folletos informativos.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'es', 'name_string' => 'Functiona - Cómo y Porqué', 'description_string' => 'Esta reunión se centra en la discusión del texto Funciona - Cómo y Porqué.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'es', 'name_string' => 'Solo por Hoy', 'description_string' => 'Esta reunión se centra en la discusión del texto Solo por Hoy.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'es', 'name_string' => 'Hombres', 'description_string' => 'A esta reunión se supone que aistan hombres solamente.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'No niños', 'description_string' => 'Por favor no traer niños a esta reunión.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'es', 'name_string' => 'Abierta', 'description_string' => 'Esta reunión está abierta a los adictos y a los no adictos por igual. Todos son bienvenidos.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'Echada', 'description_string' => 'Esta reunión tiene un formato que consiste en que cada persona que comparta escoja a la persona siguiente.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'es', 'name_string' => 'Formato que Rota', 'description_string' => 'Esta reunión tiene un formato que cambia para cada reunión.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'Round Robin', 'description_string' => 'Esta reunión tiene un orden fijo de compartir (generalmente un círculo).', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'Cámaras de Vigilancia', 'description_string' => 'Esta reunión se celebra en una localidad que tenga cámaras de vigilancia.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'es', 'name_string' => 'Orador/Discusión', 'description_string' => 'Esta reunión es conducida por un orador, después es abierta para la participación de los asistentes.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'es', 'name_string' => 'Guia Para Trabajar los Pasos', 'description_string' => 'Esta reunión se centra en la discusión del texto Guia Para Trabajar los Pasos.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'ASL', 'description_string' => 'Esta reunión proporciona intérprete (ASL) para los sordos.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'es', 'name_string' => 'Solamente Orador', 'description_string' => 'Esta reunión es de orador solamente. Otros asistentes no participan en la discusión.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'es', 'name_string' => 'Paso', 'description_string' => 'Esta reunión se centra en la discusión de los doce pasos de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'Contador de Tiempo', 'description_string' => 'Esta reunión tiene el tiempo de compartir limitado por un contador de tiempo.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'es', 'name_string' => 'Tema', 'description_string' => 'Esta reunión se basa en un tema elegido por el orador o por la conciencia del grupo.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'es', 'name_string' => 'Tradición', 'description_string' => 'Esta reunión se centra en la discusión de las Doce Tradiciones de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'es', 'name_string' => 'Taller de las Tradiciones', 'description_string' => 'Esta reunión consiste en la discusión detallada de una o más de las Doce Tradiciones de N.A.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'es', 'name_string' => 'Mujeres', 'description_string' => 'A esta reunión se supone que asistan mujeres solamente.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'es', 'name_string' => 'Silla de Ruedas', 'description_string' => 'Esta reunión es accesible por silla de ruedas.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'es', 'name_string' => 'Jovenes', 'description_string' => 'Esta reunión se centra en las necesidades de los miembros más jóvenes de NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'es', 'name_string' => 'Sin Tiempo Fijo', 'description_string' => 'No tiene tiempo fijo. Esta reunión continua hasta que cada miembro haya tenido la oportunidad de compartir.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'es', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'es', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' => 'HYBR', 'lang_enum' => 'es', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'fa', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'fa', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' => 'HYBR', 'lang_enum' => 'fa', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'fr', 'name_string' => 'Débutants', 'description_string' => 'Cette réunion est axée sur les besoins des nouveaux membres de NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'fr', 'name_string' => 'bilingue', 'description_string' => 'Cette réunion peut aider les personnes qui parlent l\'anglais et une autre langue.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'BT', 'worldid_mixed' => 'BT', 'lang_enum' => 'fr', 'name_string' => 'Texte de Base', 'description_string' => 'Cette réunion est axée sur la discussion du texte de base de Narcotiques Anonymes.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'fr', 'name_string' => 'Fermée', 'description_string' => 'Cette réunion est fermée aux non-toxicomanes. Vous pouvez y assister que si vous pensez que vous pouvez avoir un problème avec l\'abus de drogues.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Fermé durant les jours fériés.', 'description_string' => 'Cette réunion a lieu dans une local qui est généralement fermé durant les jours fériés.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'fr', 'name_string' => 'Chandelle', 'description_string' => 'Cette réunion se déroule à la chandelle.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'fr', 'name_string' => 'Enfants sous Supervision', 'description_string' => 'Les enfants bien élevés sont les bienvenus et supervisés.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'fr', 'name_string' => 'Discussion', 'description_string' => 'Cette réunion invite tous les participants à la discussion.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'fr', 'name_string' => 'Gais, lesbiennes, transsexuel(le)s, bisexuel(le)s', 'description_string' => 'Cette réunion est axée sur les besoins des membres gais, lesbiennes, transsexuel(le)s et bisexuel(le)s de NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Chroniques', 'description_string' => 'Cette réunion est axée sur les besoins des membres de NA comportant des troubles de maladies chroniques.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'fr', 'name_string' => 'Brochures', 'description_string' => 'Cette réunion est axée sur la discussion d\'une ou plusieurs brochures.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'fr', 'name_string' => 'Ça marche, Comment et Pourquoi', 'description_string' => 'Cette session met l\'accent sur la discussion de texte Ça marche, Comment et Pourquoi.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'fr', 'name_string' => 'Juste pour aujourd\'hui', 'description_string' => 'Cette session met l\'accent sur la discussion du texte Juste pour aujourd\'hui.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'fr', 'name_string' => 'Hommes', 'description_string' => 'Cette réunion est destinée à être assisté par seulement que des hommes.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Pas d\'enfant', 'description_string' => 'S\'il vous plaît, ne pas amener les enfants à cette réunion.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'fr', 'name_string' => 'Ouvert', 'description_string' => 'Cette réunion est ouverte aux toxicomanes et non-toxicomanes de même. Tous sont les bienvenus.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'À la pige', 'description_string' => 'Cette réunion a un format de discussion est que chaque personne qui discute invite la personne suivante à discuter.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'fr', 'name_string' => 'Format varié', 'description_string' => 'Cette réunion a un format qui varie à toutes les réunions.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'À la ronde', 'description_string' => 'Cette réunion a un ordre de partage fixe (généralement un cercle).', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Caméra de surveillance', 'description_string' => 'Cette réunion se tient dans un emplacement qui a des caméras de surveillance.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'fr', 'name_string' => 'Partage et ouvert', 'description_string' => 'Cette réunion a un conférencier, puis ouvert au public.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'fr', 'name_string' => 'Guides des Étapes', 'description_string' => 'Cette réunion est axée sur la discussion sur le Guide des Étapes.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Malentendants', 'description_string' => 'Cette rencontre permet l\'interprète pour les personnes malentendantes.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'fr', 'name_string' => 'Partage seulement', 'description_string' => 'Cette réunion a seulement un conférencier. Les autres participants ne participent pas à la discussion.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'fr', 'name_string' => 'Étapes NA', 'description_string' => 'Cette réunion est axée sur la discussion des Douze Étapes de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Discussion chronométrée', 'description_string' => 'Cette réunion a une durée de discussion  limitée par une minuterie pour chaque personne.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'fr', 'name_string' => 'Thématique', 'description_string' => 'Cette réunion est basée sur un thème choisi par la personne qui anime ou la conscience de groupe.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'fr', 'name_string' => 'Traditions', 'description_string' => 'Cette réunion est axée sur la discussion des Douze Traditions de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'fr', 'name_string' => 'Atelier sur les traditions', 'description_string' => 'Cette réunion est une discussion détaillée d\'une ou de plusieurs des Douze Traditions de NA', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'fr', 'name_string' => 'Femmes', 'description_string' => 'Seulement les femmes sont admises.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'fr', 'name_string' => 'Fauteuil Roulant', 'description_string' => 'Cette réunion est accessible en fauteuil roulant.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'fr', 'name_string' => 'Jeunes', 'description_string' => 'Cette réunion est axée sur les besoins des plus jeunes membres de NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Marathon', 'description_string' => 'Il n\'y a pas de durée fixe. Cette réunion se poursuit jusqu\'à ce que chaque membre a eu l\'occasion de partager.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'BK', 'worldid_mixed' => 'LIT', 'lang_enum' => 'fr', 'name_string' => 'Études de livres NA', 'description_string' => 'Livres  N.A. Approuvés', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NS', 'worldid_mixed' => 'NS', 'lang_enum' => 'fr', 'name_string' => 'Non-fumeurs', 'description_string' => 'Fumer n\'est pas permis à cette réunion.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Agnostique', 'description_string' => 'Destiné aux personnes ayant divers degrés de la foi.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'FD', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Cinq et dix', 'description_string' => 'Discussion de la cinquième étape et la dixième étape.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'AB', 'worldid_mixed' => 'QA', 'lang_enum' => 'fr', 'name_string' => 'Panier', 'description_string' => 'Un sujet est choisi parmi les suggestions placées dans un panier.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'fr', 'name_string' => 'Méditation', 'description_string' => 'Cette réunion encourage ses participants à s\'engager dans la méditation tranquille.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'RA', 'worldid_mixed' => 'RA', 'lang_enum' => 'fr', 'name_string' => 'Accés limités', 'description_string' => 'Cet emplacement impose des restrictions sur les participants.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'QA', 'worldid_mixed' => 'QA', 'lang_enum' => 'fr', 'name_string' => 'Questions et Réponses', 'description_string' => 'Les participants peuvent poser des questions et attendre des réponses des membres du groupe.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'CW', 'worldid_mixed' => 'CW', 'lang_enum' => 'fr', 'name_string' => 'Enfants bienvenus', 'description_string' => 'Les enfants sont les bienvenus à cette réunion.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'CP', 'worldid_mixed' => 'CPT', 'lang_enum' => 'fr', 'name_string' => 'Concepts', 'description_string' => 'Cette réunion est axée sur la discussion des douze concepts de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'Finlandais', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Finlandais', 'description_string' => 'Cette réunion se déroule en langue finlandaisè', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => null, 'lang_enum' => 'fr', 'name_string' => 'Anglais', 'description_string' => 'Cette réunion se déroule de langues anglais.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'fr', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'fr', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' =>  'HYBR', 'lang_enum' => 'fr', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'NV', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Nuovi venuti', 'description_string' => 'Riunione concentrata principalmente sulle necessità dei nuovi membri di NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'it', 'name_string' => 'Bilingue', 'description_string' => 'Questa riunione può essere frequentata da membri che parlano italiano e/o inglese.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'TB', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Testo base', 'description_string' => 'Riunione concentrata sulla discussione del testo base di NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'Ch.', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Chiusa', 'description_string' => 'Riunione chiusa ai non dipendenti. Dovrebbe frequentarla soltanto chi crede di avere un problema con le sostanze d\'abuso.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'SF', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Sospesa nei giorni festivi', 'description_string' => 'Questa riunione si tiene in locali che di solito sono chiusi nei giorni festivi e di vacanza.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'LC', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Lume di candela', 'description_string' => 'Questa riunione si tiene a lume di candela per favorire la meditazione.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'BS', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Bambini sotto supervisione', 'description_string' => 'Sono ammessi bambini senza problemi di comportamento e sotto supervisione.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'Disc.', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Discussione', 'description_string' => 'Tutti i partecipanti sono invitati a condividere.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'it', 'name_string' => 'Spagnolo', 'description_string' => 'Riunione in lingua spagnolo.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 14, 'key_string' => 'SPO', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Solo per oggi', 'description_string' => 'Riunione in cui si discutono i temi delle meditazioni quotidiane del libro "Solo per oggi".', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'U', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Uomini', 'description_string' => 'Riunioni per soli uomini.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'Ap.', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Aperta', 'description_string' => 'Riunione aperta ai non dipendenti. Parenti, amici, professionisti e altri membri della società, sono benvenuti.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 23, 'key_string' => 'GLP', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Guida al lavoro sui passi', 'description_string' => 'Riunione basata sulla discussione della Guida al lavoro sui Dodici passi di NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Temp.', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Condivisioni temporizzate', 'description_string' => 'In queste riunioni il tempo di condivisione è limitato da un cronometro.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'P', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Passi', 'description_string' => 'Riunione di discussione sui Dodici passi.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'Arg.', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Riunioni a tema', 'description_string' => 'Queste riunioni si basano su un argomento prescelto.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'T', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Tradizioni', 'description_string' => 'Riunione di discussione sulle Dodici tradizioni.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Workshop sulle Dodici tradizioni', 'description_string' => 'Riunioni in cui si discute dettagliatamente su una o più delle Dodici tradizioni.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'D', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Donne', 'description_string' => 'Riunione solo donne.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'SR', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Sedia a rotelle', 'description_string' => 'Riunione accessibile per chi ha la sedia a rotelle.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 35, 'key_string' => 'M', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Maratona', 'description_string' => 'Durata non prestabilita. La riunione prosegue finché tutti i presenti hanno da condividere.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NF', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Non fumatori', 'description_string' => 'In queste riunioni non è consentito fumare.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'TS', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Tema a sorpresa', 'description_string' => 'L\'argomento su cui condividere è scritto su un biglietto o altro supporto (es. un bastoncino di legno) ed estratto a caso da ciascun membro.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'M', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Meditazione', 'description_string' => 'In questa riunione sono poste restrizioni alle modalità di partecipazione.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'D/R', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Domande e risposteq', 'description_string' => 'I partecipanti possono fare domande e attenderne la risposta dagli altri membri del gruppo.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'Ba', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Bambini', 'description_string' => 'I bambini sono benvenuti in queste riunioni.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'C', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Concetti di servizio', 'description_string' => 'Riunioni basate sulla discussione dei Dodici concetti per il servizio in NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 51, 'key_string' => 'VP', 'worldid_mixed' => null, 'lang_enum' => 'it', 'name_string' => 'Vivere puliti', 'description_string' => 'Riunioni di discussione sul libro "Vivere puliti - Il viaggio continua".', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'it', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'it', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' =>  'HYBR', 'lang_enum' => 'it', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'pl', 'name_string' => 'Nowoprzybyli', 'description_string' => 'Mityng koncentruje się na potrzebach nowyh członków NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pl', 'name_string' => 'Wielojęzykowość', 'description_string' => 'Na tym mityngu mogą uczęszczać osoby posługujące się językiem angielskim i innymi.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'BT', 'worldid_mixed' => 'BT', 'lang_enum' => 'pl', 'name_string' => 'Tekst Podstawowy', 'description_string' => 'Mityng koncentruje się na dyskusjach o Tekście Podstawowym Anonimowych Narkomanów.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'pl', 'name_string' => 'Mityng zamknięty', 'description_string' => 'Mityng zamknięty. Wyłącznie dla osób uzależnionych i tych, które chcą przestać brać.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => 'CH', 'lang_enum' => 'pl', 'name_string' => 'Zamknięty w święta', 'description_string' => 'Mityng odbywa się w miejscu, które zwykle jest zamknięte w dni wolne od pracy/wakacje.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'pl', 'name_string' => 'Świeczka', 'description_string' => 'Ten mityng odbywa się przy blasku świecy.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'pl', 'name_string' => 'Dzieci pod opieką', 'description_string' => 'Dzieci uzależnionych mile widziane pod warunkiem odpowiedniego zachowania.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'pl', 'name_string' => 'Dyskusja', 'description_string' => 'Mityng dla wszystkich chętnych.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pl', 'name_string' => 'Hiszpański', 'description_string' => 'Mityng odbywa się w języku hiszpańskim.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'pl', 'name_string' => 'LGBTQ', 'description_string' => 'Mityng koncentruje się na członkach wspólnoty należących do społeczności LGBT.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Choroba', 'description_string' => 'Mityng koncentruje się na potrzebach przewlekle chorych członków NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'pl', 'name_string' => 'Broszura Informacyjna', 'description_string' => 'Mityng koncentruje się na dyskusji nad jedną z Broszur Międzynarodowych.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'pl', 'name_string' => 'To Działa - Jak i Dlaczego', 'description_string' => 'Mityng koncentruje się na dyskusji nad tekstem z "To Działa - Jak i Dlaczego".', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'pl', 'name_string' => 'Właśnie Dzisiaj', 'description_string' => 'Mityng koncentruje się na dyskusji nad tekstem z "Właśnie dzisiaj".', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'pl', 'name_string' => 'Mężczyźni', 'description_string' => 'Mityng wyłącznie dla mężczyzn.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => 'NC', 'lang_enum' => 'pl', 'name_string' => 'Bez Dzieci', 'description_string' => 'Prosimy, by nie przyprowadzać dzieci na ten mityng.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'pl', 'name_string' => 'Otwarty', 'description_string' => 'Mityng otwarty dla uzależnionych i nieuzależnionych. Wszyscy są mile widziani.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Pitch', 'description_string' => 'Na tym mityngu obowiązuje format, w którym osoba, dzieląca się doświadczeniem, wybiera kolejną osobę.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'pl', 'name_string' => 'Zmienny format', 'description_string' => 'Format tego mityngu zmienia się co mityng.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Round Robin', 'description_string' => 'Na tym mityngu jest ustalona kolejność dzielenia się doświadczeniem (zwykle w koło)', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Kamery bezpieczeństwa', 'description_string' => 'Mityng odbywa się w miejscu, w którym zamontowane są kamery bezpieczeństwa.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'pl', 'name_string' => 'Spikerka/dyskusja', 'description_string' => 'Mityng rozpoczynany jest wypowiedzią spikera, a następnie jest otwarty do dzielenia się przez resztę uczestników.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'pl', 'name_string' => 'Przewodnik pracy nad Krokami', 'description_string' => 'Mityng koncentruje się na dyskusji nad tekstem z "Przewodnika do pracy nad Krokami".', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'ASL', 'description_string' => 'W tym mityngu bierze udział tłumacz języka migowego dla osób niesłyszących.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'pl', 'name_string' => 'Tylko spikerka', 'description_string' => 'Mityng składa się tylko z wypowiedzi spikera. Inni uczestnicy nie dzielą się doświadczeniem.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'pl', 'name_string' => 'Kroki', 'description_string' => 'Mityng koncentruje się na dyskusji nad Dwunastoma Krokami Anonimowych Narkomanów.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Licznik czasu', 'description_string' => 'Na tym mitngu czas wypowiedzi jest kontrolowany przez licznik czasu.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'pl', 'name_string' => 'Dowolny temat', 'description_string' => 'Temat tego mityngu jest wybierany przez spikera lub przez sumienie grupy.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'pl', 'name_string' => 'Tradycje', 'description_string' => 'Mityng koncentruje się na dyskusji nad Dwunastoma Tradycjami NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'pl', 'name_string' => 'Warsztaty z tradycji', 'description_string' => 'Mityng koncentruje się na wnikliwej analizje jednej lub wielu z Dwunastu Tradycji Anonimowych Narkomanów', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'pl', 'name_string' => 'Kobiety', 'description_string' => 'Mityng przeznaczony jedynie dla kobiet.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'pl', 'name_string' => 'Wózki inwalidzkie', 'description_string' => 'Mityng wyposażony w łatwy dostęp dla wózków inwalidzkich.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'pl', 'name_string' => 'Młodzi ludzie', 'description_string' => 'Mityng koncentruje się na dyskusjach nad potrzebami najmłodszych członków NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Bez końca', 'description_string' => 'Mityng bez ustalonej długości. Trwa tak długo, jak długo są na nim uczestnicy.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'BK', 'worldid_mixed' => 'LIT', 'lang_enum' => 'pl', 'name_string' => 'Analiza książek', 'description_string' => 'Analiza oficjalnych książek Anonimowych Narkomanów', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NS', 'worldid_mixed' => 'NS', 'lang_enum' => 'pl', 'name_string' => 'Zakac palenia', 'description_string' => 'Palenie w trakcie tego mityngu jest zabronione.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Agnostycy', 'description_string' => 'Mityng dla ludzi o zróżnicowanych stopniach wiary.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'FD', 'worldid_mixed' => null, 'lang_enum' => 'pl', 'name_string' => 'Piąty i dziesiąty krok', 'description_string' => 'Dyskusja nad piątym i dziesiątym krokiem Anonimowych Narkomanów', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'AB', 'worldid_mixed' => 'QA', 'lang_enum' => 'pl', 'name_string' => 'Temat z koszyka', 'description_string' => 'Temat mityngu wybierany jest spośród zaproponowanych niejawnie przez grupę.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'pl', 'name_string' => 'Medytacja', 'description_string' => 'Uczestnicy tego mityngu zachęcani są do wzięcia udziału w cichej medytacji.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'RA', 'worldid_mixed' => 'RA', 'lang_enum' => 'pl', 'name_string' => 'Ograniczone uczestnictwo', 'description_string' => 'Miejsce odbywania się mityngu nakłada ograniczenia na to, kto może wziąć udział w mityngu.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'QA', 'worldid_mixed' => 'QA', 'lang_enum' => 'pl', 'name_string' => 'Pytania i odpowiedzi', 'description_string' => 'Uczestnicy mogą zadawać pytania i oczekiwać odpowiedzi od innych uczestników.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'CW', 'worldid_mixed' => 'CW', 'lang_enum' => 'pl', 'name_string' => 'Dzieci mile widziane', 'description_string' => 'Dzieci są mile widziane.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'CP', 'worldid_mixed' => 'CPT', 'lang_enum' => 'pl', 'name_string' => 'Koncepcje', 'description_string' => 'Mityng koncentruje się na dyskusji nad Dwunastoma Koncepcjami Anonimowych Narkomanów.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'FIN', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pl', 'name_string' => 'Fiński', 'description_string' => 'Mityng odbywa się w języku fińskim', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pl', 'name_string' => 'Anglojęzyczny', 'description_string' => 'Mityng odbywa się w języku angielskim.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pl', 'name_string' => 'Perski', 'description_string' => 'Mityng odbywa się w języku perskim', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 49, 'key_string' => 'L/R', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pl', 'name_string' => 'Litewski/rosyjski', 'description_string' => 'Mityng odbywa się w języku litewskim/rosyjskim', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 51, 'key_string' => 'LC', 'worldid_mixed' => 'LC', 'lang_enum' => 'pl', 'name_string' => 'Życie w czystości', 'description_string' => 'Mityng koncentruje się na dyskusji nad tekstem z "Życie w czystości: Podróż trwa nadal".', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 52, 'key_string' => 'GP', 'worldid_mixed' => 'GP', 'lang_enum' => 'pl', 'name_string' => 'Guiding Principles', 'description_string' => 'Mityng koncentruje się na dyskusji nad tekstem z "Guiding Principles - The Spirit of Our Traditions".', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'pl', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'pl', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' =>  'HYBR', 'lang_enum' => 'pl', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'RC', 'worldid_mixed' => 'BEG', 'lang_enum' => 'pt', 'name_string' => 'Recém-chegados', 'description_string' => 'Esta reunião tem foco nas necessidades de novos membros em NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pt', 'name_string' => 'Bilíngue', 'description_string' => 'Reunião pode acontecer em duas línguas além de Português.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'TB', 'worldid_mixed' => 'BT', 'lang_enum' => 'pt', 'name_string' => 'Texto Básico', 'description_string' => 'Esta reunião tem foco no debate sobre o Texto Básico de Narcóticos Anônimos.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'F', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'pt', 'name_string' => 'Fechada', 'description_string' => 'Esta reunião fechada para não adictos. Você deve ir apenas se acredita ter problemas com abuso de substâncias.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'FF', 'worldid_mixed' => 'CH', 'lang_enum' => 'pt', 'name_string' => 'Fechada em feriados', 'description_string' => 'Esta reunião acontece em local que geralmente é fechado em feirados.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'VL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'pt', 'name_string' => 'Luz de velas', 'description_string' => 'Esta reunião acontece à luz de velas.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CA', 'worldid_mixed' => '', 'lang_enum' => 'pt', 'name_string' => 'Criança sob supervisão', 'description_string' => 'Bem-comportadas, crianças sob supervisão são bem-vindas.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'pt', 'name_string' => 'Discussão', 'description_string' => 'Esta reunião convida a participação de todos.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pt', 'name_string' => 'Espanhol', 'description_string' => 'Esta reunião acontece em Espanhol.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 10, 'key_string' => 'LGBT', 'worldid_mixed' => 'GL', 'lang_enum' => 'pt', 'name_string' => 'LGBTQ+', 'description_string' => 'Reunião de interesse LGBTQ+ em NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'DC', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Doença Crônica', 'description_string' => 'Esta reunião tem foco nos interesses especiais de pessoas sofrendo de doenças crônicas.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'PI', 'lang_enum' => 'pt', 'name_string' => 'Panfleto Informativo', 'description_string' => 'Esta reunião tem foco na discussão sobre um ou mais IPs ou Panfletos Informativos.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'FUN', 'worldid_mixed' => 'IW', 'lang_enum' => 'pt', 'name_string' => 'Funciona - Como e Por quê', 'description_string' => 'Esta reunião tem foco na discussão do texto do livro Funciona - Como e Por quê.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'SPH', 'worldid_mixed' => 'JFT', 'lang_enum' => 'pt', 'name_string' => 'Só Por Hoje', 'description_string' => 'Esta reunião tem foco na discussão do texto do livro Só Por Hoje.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'H', 'worldid_mixed' => 'M', 'lang_enum' => 'pt', 'name_string' => 'Homens', 'description_string' => 'Reunião de interesse masculino em NA', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'PC', 'worldid_mixed' => 'NC', 'lang_enum' => 'pt', 'name_string' => 'Proibido crianças', 'description_string' => 'Por gentileza não trazer crianças a essa reunião.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'A', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'pt', 'name_string' => 'Aberta', 'description_string' => 'Esta reunião é aberta para adictos e não-adictos. Todos são bem-vindos.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Ind', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Indicação', 'description_string' => 'Esta reunião tem um formato que consiste que cada pessoa que partilha escolhe a próxima pessoa a partilhar.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'FR', 'worldid_mixed' => 'VAR', 'lang_enum' => 'pt', 'name_string' => 'Formato Rotativo', 'description_string' => 'Esta reunião muda seu formato a cada reunião.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Round Robin', 'description_string' => 'Esta reunião tem um formato fixo de partilha (geralmente em círculo.)', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'CV', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Câmera de vigilância', 'description_string' => 'Esta reunião acontece em ambiente que tem câmeras de vigilância.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'TD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'pt', 'name_string' => 'Temática/Discussão', 'description_string' => 'Esta reunião tem um orador, em seguida é aberta a participação dos membros', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'EP', 'worldid_mixed' => 'SWG', 'lang_enum' => 'pt', 'name_string' => 'Estudo de Passos', 'description_string' => 'Esta reunião é de estudo dos passos através do Guia Para Trabalhar os Passos de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'LS', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'LSB', 'description_string' => 'Esta reunião acontece com ajuda de intérprete de LIBRAS (Língua Brasileira de Sinais).', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'TM', 'worldid_mixed' => 'SPK', 'lang_enum' => 'pt', 'name_string' => 'Temática', 'description_string' => 'Esta reunião é do tipo temática. Não há participação dos membros na discussão.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'PS', 'worldid_mixed' => 'STEP', 'lang_enum' => 'pt', 'name_string' => 'Passos', 'description_string' => 'Esta reunião é de discussão dos 12 Passos de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'TP', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Tempo de Partilha', 'description_string' => 'Esta reunião tem seu tempo de partilha controlado por relógio.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'pt', 'name_string' => 'Tópico', 'description_string' => 'Esta reunião é baseada em tópico escolhida por um orador ou por consciência de grupo.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'pt', 'name_string' => 'Tradições', 'description_string' => 'Esta reunião tem foco em discussão das 12 Tradições de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'pt', 'name_string' => 'Workshop de Tradições', 'description_string' => 'Esta reunião envolve uma discussão mais detalhada de uma ou mais das Tradições de N.A.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'M', 'worldid_mixed' => 'W', 'lang_enum' => 'pt', 'name_string' => 'Mulheres', 'description_string' => 'Reunião de interesse feminino em NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'CadT', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'pt', 'name_string' => 'Cadeirante Total', 'description_string' => 'Esta reunião tem acesso total a cadeirantes.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'Jv', 'worldid_mixed' => 'Y', 'lang_enum' => 'pt', 'name_string' => 'Jovens', 'description_string' => 'Esta reunião tem foco nos interesses de membros jovens em NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'UP', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Último Partilhar', 'description_string' => 'Sem duração fixa. A reunião continua até todos os presentes partilharem.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'EL', 'worldid_mixed' => 'LIT', 'lang_enum' => 'pt', 'name_string' => 'Estudo de Literatura', 'description_string' => 'Reunião de estudo de literaturas aprovadas de NA', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NF', 'worldid_mixed' => 'NS', 'lang_enum' => 'pt', 'name_string' => 'Proibido Fumar', 'description_string' => 'Não é permitido fumar nessa reunião.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Agnóstico', 'description_string' => 'Destinada a pessoas com diferentes graus de fé.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'QD', 'worldid_mixed' => null, 'lang_enum' => 'pt', 'name_string' => 'Quinto e Décimo', 'description_string' => 'Reunião de discussão sobre o Quinto e Décimo Passos', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'ST', 'worldid_mixed' => 'QA', 'lang_enum' => 'pt', 'name_string' => 'Sorteio de Tópico', 'description_string' => 'Um tópico é escolhido através de sugestões sorteadas.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'pt', 'name_string' => 'Meditação', 'description_string' => 'Esta reunião incentiva seus participantes a se envolverem em meditação silenciosa.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'AR', 'worldid_mixed' => 'RA', 'lang_enum' => 'pt', 'name_string' => 'Acesso Restrito', 'description_string' => 'Esta reunião esta em local que impõe restrição de acesso às pessoas.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'PR', 'worldid_mixed' => 'QA', 'lang_enum' => 'pt', 'name_string' => 'Perguntas e Respostas', 'description_string' => 'Os participantes podem fazer perguntas e esperar respostas dos membros do grupo.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'PC', 'worldid_mixed' => 'CW', 'lang_enum' => 'pt', 'name_string' => 'Permitido Crianças', 'description_string' => 'Crianças são bem-vindas a essa reunião.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'Con', 'worldid_mixed' => 'CPT', 'lang_enum' => 'pt', 'name_string' => 'Conceitos', 'description_string' => 'Esta reunião tem foco na discussão dos Doze Conceitos de NA.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'FIN', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pt', 'name_string' => 'Filandês', 'description_string' => 'Reunião em língua filandesa', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pt', 'name_string' => 'Inglês', 'description_string' => 'Reunião em língua inglesa.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pt', 'name_string' => 'Persa', 'description_string' => 'Reunião em língua persa', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 49, 'key_string' => 'L/R', 'worldid_mixed' => 'LANG', 'lang_enum' => 'pt', 'name_string' => 'Lituano/Russo', 'description_string' => 'Reunião em Lituano/Russo', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 51, 'key_string' => 'VL', 'worldid_mixed' => 'LC', 'lang_enum' => 'pt', 'name_string' => 'Vivendo Limpo', 'description_string' => 'Esta é uma reunião de discussão do livro Vivendo Limpo-A Jornada Continua.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 52, 'key_string' => 'GP', 'worldid_mixed' => 'GP', 'lang_enum' => 'pt', 'name_string' => 'Guia de Princípios', 'description_string' => 'Esta é uma reunião baseada no livro Guia de Princípios - O Espírito das Nossas Tradições .', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 53, 'key_string' => 'CadP', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'pt', 'name_string' => 'Cadeirante Parcial', 'description_string' => 'Esta reunião tem acesso parcial a cadeirante.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'pt', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'pt', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' =>  'HYBR', 'lang_enum' => 'pt', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'ru', 'name_string' => 'Начинающие', 'description_string' => 'Эта встреча посвящена потребностям новых членов NA.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'ru', 'name_string' => 'Двуязычное ', 'description_string' => ' На этом совещании могут присутствов Базового Текста Анонимных Наркоманов', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'ru', 'name_string' => 'Закрытая', 'description_string' => 'Эта встреча закрыта для не наркоманов. Вам следует присутствовать только в том случае, если вы считаете, что у вас могут быть проблемы со злоупотреблением психоактивными веществами.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => 'CH', 'lang_enum' => 'ru', 'name_string' => 'Закрыто по праздникам', 'description_string' => 'Эта встреча собирается в учреждении, которое обычно закрыто в праздничные дни.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'ru', 'name_string' => 'Искусственное освещение', 'description_string' => 'Эта встреча проводится при свечах.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'ru', 'name_string' => 'Дети под присмотром', 'description_string' => 'Добро пожаловать, хорошо воспитанные дети приветствуются.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'ru', 'name_string' => 'Обсуждение', 'description_string' => 'Эта встреча приглашает к участию всех участников.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'ru', 'name_string' => 'Испанский', 'description_string' => 'Эта встреча проводится на испанском языке.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'ru', 'name_string' => 'Геи / Лесбиянки / трансгендеры', 'description_string' => 'Эта встреча посвящена потребностям геев, лесбиянок и транссексуальных членов АН.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Болезнь', 'description_string' => 'Эта встреча посвящена потребностям членов АН с хроническим заболеванием.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'ru', 'name_string' => 'Информационная брошюра', 'description_string' => 'Эта встреча посвящена обсуждению одной или нескольких информационных брошюр.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'ru', 'name_string' => 'Это работает - как и почему', 'description_string' => 'Эта встреча посвящена обсуждению текста «Как это работает - как и почему».', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'ru', 'name_string' => 'Только сегодня', 'description_string' => 'Эта встреча посвящена обсуждению текста "Только Сегодня"', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'ru', 'name_string' => 'Мужчины', 'description_string' => 'Эта встреча предназначена только для мужчин.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => 'NC', 'lang_enum' => 'ru', 'name_string' => 'Без детей', 'description_string' => 'Пожалуйста, не приводите детей на эту встречу.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'ru', 'name_string' => 'Открытая', 'description_string' => 'Эта встреча открыта как для наркоманов, так и для не наркоманов. Все приветствуются.', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Питч', 'description_string' => 'Эта встреча имеет формат, который состоит из каждого участника, который разделяет выбор следующего участника.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'ru', 'name_string' => 'Ротация', 'description_string' => 'Эта встреча имеет формат, который изменяется для каждой встречи.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Говорим по кругу', 'description_string' => 'Эта встреча имеет фиксированный порядок обмена опытом (высказывания по кругу.)', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Камеры наблюдения', 'description_string' => 'Эта встреча проводится в учреждении с камерами наблюдения.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'ru', 'name_string' => 'Спикерская / Обсуждение', 'description_string' => 'Это спикерская, а затем время для обсуждений.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'ru', 'name_string' => 'Руководство по Шагам АН', 'description_string' => 'Эта встреча посвящена обсуждению текста руководства по шагам АН.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Для глухих', 'description_string' => 'Эта встреча предоставляет переводчика американского языка жестов (ASL) для глухих.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'ru', 'name_string' => 'Только спикерская', 'description_string' => 'Только спикерская. Другие участники не участвуют в обсуждении.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'ru', 'name_string' => 'Шаги', 'description_string' => 'Эта встреча посвящена обсуждению Двенадцати Шагов АН.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Таймер', 'description_string' => 'Время этой встречи ограничено таймером.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'ru', 'name_string' => 'Тема', 'description_string' => 'Эта встреча основана на теме, выбранной ведущим или групповым.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'ru', 'name_string' => 'Традиции', 'description_string' => 'Эта встреча посвящена обсуждению Двенадцати Традиций АН.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'ru', 'name_string' => 'Мастерская Традиций', 'description_string' => 'Эта встреча включает в себя подробное обсуждение одной или нескольких из двенадцати традиций А.Н.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'ru', 'name_string' => 'Женская', 'description_string' => 'Эта встреча предназначена для участия только женщин.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'ru', 'name_string' => 'Инвалидное кресло', 'description_string' => 'Эта встреча доступна для инвалидов.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'ru', 'name_string' => 'Молодые люди', 'description_string' => 'Эта встреча ориентирована на потребности молодых членов АН.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Неограниченная', 'description_string' => 'Нет фиксированной продолжительности. Встреча продолжается до тех пор, пока все присутствующие не смогут поделиться опытом.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'BK', 'worldid_mixed' => 'LIT', 'lang_enum' => 'ru', 'name_string' => 'Книжное обучение', 'description_string' => 'Утвержденные книги А.Н.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NS', 'worldid_mixed' => 'NS', 'lang_enum' => 'ru', 'name_string' => 'Не курить', 'description_string' => 'Курение запрещено на этой встрече.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Агностики', 'description_string' => 'Предназначен для людей с разной степенью веры.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'FD', 'worldid_mixed' => null, 'lang_enum' => 'ru', 'name_string' => 'Пятый и Десятый', 'description_string' => 'Обсуждение пятого шага и десятого шага', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'AB', 'worldid_mixed' => 'QA', 'lang_enum' => 'ru', 'name_string' => 'Коробочка', 'description_string' => 'Тема выбирается из предложений, помещенных в коробочку.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'ru', 'name_string' => 'Медитация', 'description_string' => 'Эта встреча поощряет ее участников заниматься тихой медитацией.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'RA', 'worldid_mixed' => 'RA', 'lang_enum' => 'ru', 'name_string' => 'Ограниченная Посещаемость', 'description_string' => 'Эта встреча накладывает ограничения на посетителей.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'QA', 'worldid_mixed' => 'QA', 'lang_enum' => 'ru', 'name_string' => 'Вопрос и ответ', 'description_string' => 'Участники могут задавать вопросы и ожидать ответов от членов группы.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'CW', 'worldid_mixed' => 'CW', 'lang_enum' => 'ru', 'name_string' => 'Дети - добро пожаловать', 'description_string' => 'Дети приветствуются на этой встрече.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'CP', 'worldid_mixed' => 'CPT', 'lang_enum' => 'ru', 'name_string' => 'Концепции', 'description_string' => 'Эта встреча посвящена обсуждению двенадцати концепций А.Н.', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'FIN', 'worldid_mixed' => 'LANG', 'lang_enum' => 'ru', 'name_string' => 'Финский', 'description_string' => 'финноязычная встреча', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => 'LANG', 'lang_enum' => 'ru', 'name_string' => 'Англогоязычный', 'description_string' => 'На его собрании могут присутствовать носители английского языка.', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => 'LANG', 'lang_enum' => 'ru', 'name_string' => 'Персидский', 'description_string' => 'Собрание проводится на Персидском языке', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 49, 'key_string' => 'L/R', 'worldid_mixed' => 'LANG', 'lang_enum' => 'ru', 'name_string' => 'Русский\литовский', 'description_string' => 'Русскоговорящие собрания АН', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 51, 'key_string' => 'LC', 'worldid_mixed' => 'LC', 'lang_enum' => 'ru', 'name_string' => 'Жить Чистыми', 'description_string' => 'Это обсуждение книги АН «Живи чисто - путешествие продолжается».', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 52, 'key_string' => 'GP', 'worldid_mixed' => 'GP', 'lang_enum' => 'ru', 'name_string' => 'Руководящие принципы', 'description_string' => 'Это обсуждение книги АН «Руководящие принципы - дух наших традиций».', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' =>  'VM', 'lang_enum' => 'ru', 'name_string' => 'Виртуальная встреча', 'description_string' => 'Собираемся онлайн', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' =>  'TC', 'lang_enum' => 'ru', 'name_string' => 'Временно закрыто', 'description_string' => 'Объект временно закрыт', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' =>  'HYBR', 'lang_enum' => 'ru', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 4, 'key_string' => 'S', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'sv', 'name_string' => 'Slutet möte', 'description_string' => 'Ett slutet NA möte är för de individer som identifierar sig som beroende eller för de som är osäkra och tror att de kanske har drogproblem.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'sv', 'name_string' => 'Mansmöte', 'description_string' => 'Detta möte är endast öppet för män.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'Ö', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'sv', 'name_string' => 'Öppet möte', 'description_string' => 'Ett öppet möte är ett NA-möte där vem som helst som är intresserad av hur vi har funnit tillfrisknande från beroendesjukdomen kan närvara.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => null, 'lang_enum' => 'sv', 'name_string' => 'Engelska', 'description_string' => 'Engelsktalande möte', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => null, 'lang_enum' => 'sv', 'name_string' => 'Persiskt', 'description_string' => 'Persiskt möte', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'K', 'worldid_mixed' => 'W', 'lang_enum' => 'sv', 'name_string' => 'Kvinnomöte', 'description_string' => 'Detta möte är endast öppet för kvinnor.', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'RL', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'sv', 'name_string' => 'Rullstolsvänlig lokal', 'description_string' => 'Detta möte är tillgängligt för rullstolsbundna.', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => null, 'lang_enum' => 'sv', 'name_string' => 'Engelska', 'description_string' => 'Engelsktalande möte', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 54, 'key_string' => 'VM', 'worldid_mixed' => 'VM', 'lang_enum' => 'sv', 'name_string' => 'Virtual Meeting', 'description_string' => 'Meets Virtually', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 55, 'key_string' => 'TC', 'worldid_mixed' => 'TC', 'lang_enum' => 'sv', 'name_string' => 'Temporarily Closed Facility', 'description_string' => 'Facility is Temporarily Closed', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 56, 'key_string' => 'HY', 'worldid_mixed' =>  'HYBR', 'lang_enum' => 'sv', 'name_string' => 'Hybrid Meeting', 'description_string' => 'Meets Virtually and In-person', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 1, 'key_string' => 'B', 'worldid_mixed' => 'BEG', 'lang_enum' => 'fa', 'name_string' => 'تازه واردان', 'description_string' => 'این جلسه بر روی نیازهای تازه واردان در معتادان گمنام متمرکز میباشد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 2, 'key_string' => 'BL', 'worldid_mixed' => 'LANG', 'lang_enum' => 'fa', 'name_string' => 'دو زبانه', 'description_string' => 'این جلسه پذیرای شرکت کنندگان انگلیسی زبان و دیگر زبان ها میباشد', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 3, 'key_string' => 'BT', 'worldid_mixed' => 'BT', 'lang_enum' => 'fa', 'name_string' => 'کتاب پایه', 'description_string' => 'این جلسه متمرکز بر روی بحث درباره کتاب پایه معتادان گمنام میباشد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 4, 'key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'fa', 'name_string' => 'بسته', 'description_string' => 'این جلسه برای افراد غیر معتاد بسته میباشد. شما تنها اگر فکر میکنید با مواد خدر مشکل دارید میتوانید شرکت کنید', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 5, 'key_string' => 'CH', 'worldid_mixed' => 'CH', 'lang_enum' => 'fa', 'name_string' => 'بسته در روزهای تعطیل', 'description_string' => 'این جلسات در روزهای تعطیل برگزار نمیگردد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 6, 'key_string' => 'CL', 'worldid_mixed' => 'CAN', 'lang_enum' => 'fa', 'name_string' => 'شمع روشن', 'description_string' => 'این جلسه بهمراه شمع روشن برگزار میگردد', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 7, 'key_string' => 'CS', 'worldid_mixed' => '', 'lang_enum' => 'fa', 'name_string' => 'کودکان بی سرپرست', 'description_string' => 'خوش رفتاری', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 8, 'key_string' => 'D', 'worldid_mixed' => 'DISC', 'lang_enum' => 'fa', 'name_string' => 'بحث و گفتگو', 'description_string' => 'این جلسه از تمامی شرکت کنندگان دعوت به بحث میکند', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 9, 'key_string' => 'ES', 'worldid_mixed' => 'LANG', 'lang_enum' => 'fa', 'name_string' => 'اسپانیایی', 'description_string' => 'این جلسه به زبان اسپانیایی برگزار میگردد', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 10, 'key_string' => 'GL', 'worldid_mixed' => 'GL', 'lang_enum' => 'fa', 'name_string' => 'مردان همجنس باز/زنان همجنس باز/تغییر جنسیتی ها', 'description_string' => 'این جلسه به نیازهای همجنس بازان/همجنس خواهان میپردازد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 11, 'key_string' => 'IL', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'بیماران', 'description_string' => 'این جلسه به نیازهای اعضا با بیماری های مزمن متمرکز میباشد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 12, 'key_string' => 'IP', 'worldid_mixed' => 'IP', 'lang_enum' => 'fa', 'name_string' => 'پمفلت های اطلاعاتی', 'description_string' => 'این جلسه به بررسی و بحث در مورد یک یا چند پمفلت متمرکز میباشد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 13, 'key_string' => 'IW', 'worldid_mixed' => 'IW', 'lang_enum' => 'fa', 'name_string' => 'چگونگی عملکرد ', 'description_string' => 'این جلسه با موضوع بحث در مورد کتاب چگونگی عملکرد برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 14, 'key_string' => 'JT', 'worldid_mixed' => 'JFT', 'lang_enum' => 'fa', 'name_string' => 'فقط برای امروز', 'description_string' => 'این جلسه با موضوع بحث درمورد کتاب فقط برای امروز متمرکز میباشد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 15, 'key_string' => 'M', 'worldid_mixed' => 'M', 'lang_enum' => 'fa', 'name_string' => 'مردان', 'description_string' => 'این جلسه فقط مخصوص آقایان مباشد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 16, 'key_string' => 'NC', 'worldid_mixed' => 'NC', 'lang_enum' => 'fa', 'name_string' => 'ممنوعیت ورود کودکان', 'description_string' => 'لطفاً کودکان را به این جلسه نیاورید', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 17, 'key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'fa', 'name_string' => 'باز', 'description_string' => 'این جلسه برای کلیه اعضا معتاد و همچنین غیر معتادان باز میباشد', 'format_type_enum' => 'O'],
                ['shared_id_bigint' => 18, 'key_string' => 'Pi', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'انتخابی', 'description_string' => 'فورمت این جلسه بصورتیست که هر مشارکت کننده میتواند نفر بعدی را جهت مشارکت انتخاب نماید', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 19, 'key_string' => 'RF', 'worldid_mixed' => 'VAR', 'lang_enum' => 'fa', 'name_string' => 'فورمت چرخشی', 'description_string' => 'فورمت این جلسه در هر جلسه متغیر میباشد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 20, 'key_string' => 'Rr', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'مشارکت موضوع دار', 'description_string' => 'این جلسه دارای یکسری موضوعات خاص میباشد (معمولاً بصورت چرخشی)', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 21, 'key_string' => 'SC', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'دوربین مداربسته', 'description_string' => 'این جلسه در مکانهای مجهز به دوربین مدار بسته برگزار میگردد', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 22, 'key_string' => 'SD', 'worldid_mixed' => 'S-D', 'lang_enum' => 'fa', 'name_string' => 'سخنرانی/بحث', 'description_string' => 'این جلسه توسط یک سخنران گردانندگی میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 23, 'key_string' => 'SG', 'worldid_mixed' => 'SWG', 'lang_enum' => 'fa', 'name_string' => 'راهنمای کارکرد قدم', 'description_string' => 'این جلسه با موضوع بررسی و بحث در مورد کتاب راهنمای کاکرد قدم برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 24, 'key_string' => 'SL', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'تفسیر به زبان انگلیسی برای ناشنوایان', 'description_string' => 'این جلسه بهمراه مفسر انگلیسی برای ناشنوایان برگزار میگردد', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 26, 'key_string' => 'So', 'worldid_mixed' => 'SPK', 'lang_enum' => 'fa', 'name_string' => 'فقط سخنرانی', 'description_string' => 'این جلسه فقط یک سخنران دارد. دیگر شرکت کنندگان حق مشارکت ندارند', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 27, 'key_string' => 'St', 'worldid_mixed' => 'STEP', 'lang_enum' => 'fa', 'name_string' => 'قدم', 'description_string' => 'این جلسه با موضوع بحث درمورد قدم های دوازده گانه معتادان گمنام برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 28, 'key_string' => 'Ti', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'زمان سنج', 'description_string' => 'در این جلسه زمان مشارکت توسط زمان سنج محاسبه و کنترل میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 29, 'key_string' => 'To', 'worldid_mixed' => 'TOP', 'lang_enum' => 'fa', 'name_string' => 'موضوع', 'description_string' => 'این جلسه برپایه موضوع انتخابی توسط یک سخنران یا وجدان گروهی برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 30, 'key_string' => 'Tr', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'fa', 'name_string' => 'سنت ها', 'description_string' => 'این جلسه با موضوع بحث درمورد سنت های دوازده گانه معتادان گمنام برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 31, 'key_string' => 'TW', 'worldid_mixed' => 'TRAD', 'lang_enum' => 'fa', 'name_string' => 'کارگاه سنت ها', 'description_string' => 'این جلسه با موضوع بررسی جزئیاتی یک یاچند سنت معتادان گمنام برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 32, 'key_string' => 'W', 'worldid_mixed' => 'W', 'lang_enum' => 'fa', 'name_string' => 'بانوان', 'description_string' => 'این جلسه فقط مخصوص خانم ها مباشد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 33, 'key_string' => 'WC', 'worldid_mixed' => 'WCHR', 'lang_enum' => 'fa', 'name_string' => 'ویلچر', 'description_string' => 'در این جلسه ویلچر در دسترس میباشد', 'format_type_enum' => 'FC2'],
                ['shared_id_bigint' => 34, 'key_string' => 'YP', 'worldid_mixed' => 'Y', 'lang_enum' => 'fa', 'name_string' => 'جوانان', 'description_string' => 'این جلسه بر روی نیازهای اعضا جوان متمرکز میباشد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 35, 'key_string' => 'OE', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'بی پایان', 'description_string' => 'بدون مدت زمان ثابت. این جلسه تا زمانی که تمامی اعضا درخواست کننده مشارکت، مشارکت نکرده باشند به اتمام نمیرسد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 36, 'key_string' => 'BK', 'worldid_mixed' => 'LIT', 'lang_enum' => 'fa', 'name_string' => 'کتاب خوانی', 'description_string' => 'کتابخوانی نشریات معتادان گمنام', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 37, 'key_string' => 'NS', 'worldid_mixed' => 'NS', 'lang_enum' => 'fa', 'name_string' => 'مصرف دخانیات ممنوع', 'description_string' => 'مصرف دخانیات در این جلسه ممنوع میباشد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 38, 'key_string' => 'Ag', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'بی اعتقادان', 'description_string' => 'جلسه مخصوص اعضا باهر میزان درجه از اعتقاد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 39, 'key_string' => 'FD', 'worldid_mixed' => null, 'lang_enum' => 'fa', 'name_string' => 'پنج و ده', 'description_string' => 'جلسه بحث و بررسی قدم های پنج و ده', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 40, 'key_string' => 'AB', 'worldid_mixed' => 'QA', 'lang_enum' => 'fa', 'name_string' => 'انتخاب موضوع از سبد', 'description_string' => 'انتخاب یک موضوع توسط پیشنهادات ارائه شده در سبد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 41, 'key_string' => 'ME', 'worldid_mixed' => 'MED', 'lang_enum' => 'fa', 'name_string' => 'مراقبه', 'description_string' => 'این جلسه اعضا شرکت کننده را به مراقبه کامل تشویق مینماید', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 42, 'key_string' => 'RA', 'worldid_mixed' => 'RA', 'lang_enum' => 'fa', 'name_string' => 'محدودیت شرکت کننده', 'description_string' => 'این جلسه دارای محدودیت شرکت کنندگان میباشد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 43, 'key_string' => 'QA', 'worldid_mixed' => 'QA', 'lang_enum' => 'fa', 'name_string' => 'پرسش و پاسخ', 'description_string' => 'اعضا میتوانند سوالات خود را مطرح نموده و منتظر دریافت پاسخ از دیگر اعضا باشند', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 44, 'key_string' => 'CW', 'worldid_mixed' => 'CW', 'lang_enum' => 'fa', 'name_string' => 'با حضور کودکان', 'description_string' => 'حضور کودکان در این جلسه بلامانع میباشد', 'format_type_enum' => 'FC3'],
                ['shared_id_bigint' => 45, 'key_string' => 'CP', 'worldid_mixed' => 'CPT', 'lang_enum' => 'fa', 'name_string' => 'مفاهیم', 'description_string' => 'این جلسه با موضوع بحث درمورد مفاهیم دوازده گانه معتادان گمنام برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 46, 'key_string' => 'FIN', 'worldid_mixed' => 'LANG', 'lang_enum' => 'fa', 'name_string' => 'فنلاندی', 'description_string' => 'جلسه به زبان فنلاندی', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 47, 'key_string' => 'ENG', 'worldid_mixed' => 'LANG', 'lang_enum' => 'fa', 'name_string' => ' انگلیسی', 'description_string' => 'این جلسه میتواند با حضور اعضا انگلیسی زبان نیز برگزار گردد', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 48, 'key_string' => 'PER', 'worldid_mixed' => 'LANG', 'lang_enum' => 'fa', 'name_string' => 'فارسی', 'description_string' => 'جلسه به زبان فارسی', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 49, 'key_string' => 'L/R', 'worldid_mixed' => 'LANG', 'lang_enum' => 'fa', 'name_string' => 'لیتوانیایی/روسی', 'description_string' => 'جلسه به زبان های لیتوانیایی/روسی', 'format_type_enum' => 'LANG'],
                ['shared_id_bigint' => 51, 'key_string' => 'LC', 'worldid_mixed' => 'LC', 'lang_enum' => 'fa', 'name_string' => 'پاک زیستن', 'description_string' => 'این جلسه با موضوع بررسی و بحث در مورد کتاب پاک زیستن - سفر ادامه دارد، برگزار میگردد', 'format_type_enum' => 'FC1'],
                ['shared_id_bigint' => 52, 'key_string' => 'GP', 'worldid_mixed' => 'GP', 'lang_enum' => 'fa', 'name_string' => 'روح سنت ها', 'description_string' => 'این جلسه با موضوع بررسی و بحث در مورد کتاب روح سنت ها برگزار میگردد', 'format_type_enum' => 'FC1'],
            ]);
        }

        if (!Schema::hasTable('comdef_meetings_main')) {
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
        }

        if (!Schema::hasTable('comdef_service_bodies')) {
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
        }

        if (Schema::hasTable('comdef_users')) {
            Schema::table('comdef_users', function (Blueprint $table) {
                $table->dateTime('last_access_datetime')->default('1970-01-01 00:00:00')->change();
            });
        } else {
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
        }

        if (!Schema::hasTable('comdef_changes')) {
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
