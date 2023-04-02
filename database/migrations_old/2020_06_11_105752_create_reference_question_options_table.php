<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferenceQuestionOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reference_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reference_question_id');
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('reference_question_id')->references('id')->on('reference_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reference_question_options');
    }
}
