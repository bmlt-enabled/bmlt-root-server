<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        if (Schema::hasTable('comdef_service_bodies')) {
            return;
        }

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
            $table->string('sb_meeting_email');
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
            $table->tinyInteger('user_level_tinyint')->default(0);
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comdef_service_bodies');
        Schema::dropIfExists('comdef_users');
    }
};
