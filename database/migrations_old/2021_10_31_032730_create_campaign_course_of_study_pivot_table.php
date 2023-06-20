<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignCourseOfStudyPivotTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_course_of_study', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id');
            $table->foreignId('course_of_study_id');

            // $table->primary(['campaign_id', 'course_of_study_id']);
			// $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
			// $table->foreign('course_of_study_id')->references('id')->on('course_of_studies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_course_of_study');
    }
}
