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
        Schema::create('assesment_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_id');
            $table->foreignId('manager_id');
            $table->foreignId('talent_id');
            $table->integer('total_score')->default(0)->nullable();
            $table->integer('talent_score')->default(0)->nullable();
            $table->integer('score_average')->default(0)->nullable();
            $table->text('talent_feedback')->nullable();
            $table->text('manager_feedback')->nullable();
            $table->text('training_suggestion')->nullable();
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
        Schema::dropIfExists('assesment_summaries');
    }
};
