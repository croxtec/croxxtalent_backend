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
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->morphs('recordable'); // For both Department and Employee
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->float('overall_score', 8, 2)->default(0);

            // Assessment metrics
            $table->float('assessment_score', 8, 2)->default(0);
            $table->float('assessment_participation_rate', 8, 2)->default(0);

            // Peer review metrics
            $table->float('peer_review_score', 8, 2)->default(0);
            $table->string('peer_review_trend')->default('stable');

            // Goal metrics
            $table->float('goal_completion_rate', 8, 2)->default(0);
            $table->float('goal_participation_rate', 8, 2)->default(0);

            // Project metrics
            $table->float('project_completion_rate', 8, 2)->default(0);
            $table->float('project_on_time_rate', 8, 2)->default(0);
            $table->float('project_participation_rate', 8, 2)->default(0);

            // Competency metrics
            $table->float('competency_average_score', 8, 2)->default(0);

            // KPI metrics
            $table->float('kpi_overall_achievement', 8, 2)->default(0);
            $table->float('kpi_technical_achievement', 8, 2)->default(0);
            $table->float('kpi_soft_achievement', 8, 2)->default(0);

            // Training metrics
            $table->float('training_score', 8, 2)->default(0);
            $table->float('training_completion_rate', 8, 2)->default(0);

            // Additional details in JSON format
            $table->json('meta_data')->nullable();


            // $table->unique(['recordable_id', 'recordable_type', 'year', 'month']);

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
        Schema::dropIfExists('performance_records');
    }
};
