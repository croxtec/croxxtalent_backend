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
        Schema::create('talent_assessment_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id');
            $table->foreignId('talent_id');

            $table->string('summary')->nullable();
            $table->string('time_taken')->nullable();
            $table->double('talent_score')->default(0)->nullable();
            $table->double('total_score')->default(0)->nullable();
            $table->double('graded_score')->default(0)->nullable();

            $table->foreignId('expert_id')->nullable();
            $table->foreignId('goal_id')->nullable();
            $table->text('talent_feedback')->nullable();
            $table->text('expert_feedback')->nullable();
            $table->string('badge_earn')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('archived_at')->nullable();
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
        Schema::dropIfExists('talent_assessment_summaries');
    }
};
