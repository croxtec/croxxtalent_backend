<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCvSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id');
            $table->foreignId('skill_id');
            $table->string('level', 50);
            $table->timestamps();

            $table->foreign('cv_id')->references('id')->on('cvs')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cv_skills');
    }
}
