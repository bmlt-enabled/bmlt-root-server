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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comdef_service_bodies');
    }
};
