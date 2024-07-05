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
        Schema::create('employer_assessment_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id');
            $table->foreignId('employee_id');
            $table->foreignId('employer_user_id');

            $table->foreignId('supervisor_id')->nullable();
            $table->string('summary')->nullable();
            $table->string('time_taken')->nullable();
            $table->integer('graded_score')->default(0)->nullable();

            $table->foreignId('goal_id')->nullable();
            $table->text('employee_feedback')->nullable();
            $table->text('supervisor_feedback')->nullable();
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
        Schema::dropIfExists('employer_assessment_feedback');
    }
};
