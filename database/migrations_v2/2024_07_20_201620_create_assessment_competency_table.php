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
        Schema::create('assessment_competency', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('competency_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('assessment_id')->references('id')->on('croxx_assessments')->onDelete('cascade');
            $table->foreign('competency_id')->references('id')->on('department_mappings')->onDelete('cascade');

            // Unique constraint to avoid duplicate entries
            // $table->unique(['assessment_id', 'competency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_competency');
    }
};
