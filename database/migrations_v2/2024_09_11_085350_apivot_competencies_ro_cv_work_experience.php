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
        Schema::create('work_experience_competencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_experience_id');
            $table->unsignedBigInteger('competency_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('work_experience_id')->references('id')->on('cv_work_experiences')->onDelete('cascade');
            $table->foreign('competency_id')->references('id')->on('competency_setups')->onDelete('cascade');

            // Unique constraint to avoid duplicate entries
            // $table->unique(['work_experience', 'competency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_experience_competencies');
    }
};
