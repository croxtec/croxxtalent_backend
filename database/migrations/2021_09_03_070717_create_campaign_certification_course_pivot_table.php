<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignCertificationCoursePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_certification_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id');
            $table->foreignId('certification_course_id');

            // $table->primary(['campaign_id', 'certification_course_id'], 'my_c_id_cc_id_primary');
			$table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
			$table->foreign('certification_course_id')->references('id')->on('certification_courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_certification_course');
    }
}
