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
        Schema::create('assesment_talent_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id');
            $table->foreignId('assessment_question_id');
            $table->foreignId('talent_id')->nullable();
            $table->foreignId('employee_id')->nullable();

            $table->text('comment')->nullable();
            $table->dateTime('period')->nullable();
            $table->string('option')->nullable();
            $table->json('options')->nullable();
            $table->text('upload')->nullable();
            $table->text('document')->nullable();

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
        Schema::dropIfExists('assesment_talent_answers');
    }
};
