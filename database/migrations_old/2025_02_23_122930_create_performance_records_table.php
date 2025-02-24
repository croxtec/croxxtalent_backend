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
            $table->morphs('recordable');
            $table->integer('year');
            $table->integer('month');

            // Assessment metrics
            $table->decimal('assessment_score', 5, 2)->default(0);
            $table->decimal('assessment_completion_rate', 5, 2)->default(0);

            // Peer review metrics
            $table->decimal('peer_review_score', 5, 2)->default(0);
            $table->decimal('peer_review_completion_rate', 5, 2)->default(0);

            // Goals metrics
            $table->decimal('goals_completion_rate', 5, 2)->default(0);
            $table->integer('goals_achieved_count')->default(0);
            $table->integer('goals_total_count')->default(0);

            // Project metrics
            $table->decimal('project_completion_rate', 5, 2)->default(0);
            $table->decimal('project_on_time_rate', 5, 2)->default(0);
            $table->integer('tasks_completed_count')->default(0);
            $table->integer('tasks_total_count')->default(0);

            // Competency metrics
            $table->decimal('competency_score', 5, 2)->default(0);
            $table->decimal('kpi_achievement_rate', 5, 2)->default(0);

            // Overall score
            $table->decimal('overall_score', 5, 2)->default(0);

            // Optional context
            $table->string('notes')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['recordable_type', 'recordable_id', 'year', 'month']);
            $table->index('overall_score');
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
