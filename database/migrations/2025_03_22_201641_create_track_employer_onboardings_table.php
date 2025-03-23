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
        Schema::create('track_employer_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id');
            $table->boolean('department_faq')->default(false);
            $table->boolean('employees_faq')->default(false);
            $table->boolean('supervisors_faq')->default(false);
            $table->boolean('assessment_faq')->default(false);
            $table->boolean('projects_faq')->default(false);
            $table->boolean('trainings_faq')->default(false);
            $table->boolean('campaigns_faq')->default(false);
            $table->boolean('candidate_faq')->default(false);
            $table->boolean('skill_gap_faq')->default(false);
            $table->boolean('competency_analysis_faq')->default(false);
            $table->boolean('department_performance_faq')->default(false);
            $table->boolean('employee_performance_faq')->default(false);
            $table->boolean('department_development_faq')->default(false);
            $table->boolean('employee_development_faq')->default(false);
            $table->boolean('assessment_report_faq')->default(false);
            $table->boolean('training_report_faq')->default(false);
            $table->boolean('competency_report_faq')->default(false);
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
        Schema::dropIfExists('track_employer_onboardings');
    }
};
