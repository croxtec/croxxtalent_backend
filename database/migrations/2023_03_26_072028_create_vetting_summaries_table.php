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
        Schema::create('vetting_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_id');
            $table->foreignId('professional_id');
            $table->foreignId('talent_id');
            $table->integer('croxxtalent_score')->default(0)->nullable();
            $table->integer('talent_score')->default(0)->nullable();
            $table->integer('score_average')->default(0)->nullable();
            $table->text('talent_feedback')->nullable();
            $table->text('professional_feedback')->nullable();
            $table->string('badge_earn')->nullable();
            $table->json('training_suggestion')->nullable();
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
        Schema::dropIfExists('vetting_summaries');
    }
};
