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
        Schema::create('service_body_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('service_body_id')->references('id_bigint')->on('comdef_service_bodies')->cascadeOnDelete();
            $table->unsignedInteger('num_groups');
            $table->unsignedInteger('num_total_meetings');
            $table->unsignedInteger('num_in_person_meetings');
            $table->unsignedInteger('num_virtual_meetings');
            $table->unsignedInteger('num_hybrid_meetings');
            $table->unsignedInteger('num_unknown_meetings');
            $table->boolean('is_latest');
            $table->index('is_latest', 'is_latest');
            $table->index(['is_latest', 'service_body_id'], 'is_latest_service_body_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('service_body_statistics');
    }
};
