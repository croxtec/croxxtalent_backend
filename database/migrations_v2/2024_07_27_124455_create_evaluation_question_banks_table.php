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
        Schema::create('evaluation_question_banks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->default(1);
            $table->unsignedBigInteger('industry_id')->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advance', 'expert']);
            $table->string('competency_name');
            $table->text('question');
            $table->string('option1');
            $table->string('option2');
            $table->string('option3')->nullable();
            $table->string('option4')->nullable();
            $table->enum('answer', ['option1', 'option2', 'option3', 'option4']);
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
        Schema::dropIfExists('evaluation_question_banks');
    }
};
