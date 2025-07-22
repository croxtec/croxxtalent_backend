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
        Schema::create('assesment_score_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id');
            $table->foreignId('assessment_id');
            $table->foreignId('employee_id');
            $table->foreignId('assesment_question_id');
            $table->integer('score');
            $table->string('comment')->nullable();
            $table->text('attachment')->nullable();
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
        Schema::dropIfExists('assesment_score_sheets');
    }
};
