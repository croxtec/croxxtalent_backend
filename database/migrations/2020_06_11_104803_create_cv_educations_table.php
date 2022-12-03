<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCvEducationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id');
            $table->string('school', 100);
            $table->foreignId('course_of_study_id');
            $table->foreignId('degree_id');
            $table->string('city', 255)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('cv_id')->references('id')->on('cvs')->onDelete('cascade');
            $table->foreign('course_of_study_id')->references('id')->on('course_of_studies');
            $table->foreign('degree_id')->references('id')->on('degrees');
            $table->foreign('country_code')->references('code')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cv_educations');
    }
}
