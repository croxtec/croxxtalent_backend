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
        Schema::create('goal_assigned_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('project_goals')->cascadeOnDelete(); // Goal being assigned
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete(); // Employee to be assigned
            $table->foreignId('assigned_by')->constrained('employees')->cascadeOnDelete(); // Team lead assigning task
            $table->timestamp('assigned_at')->nullable(); // When the assignment was made
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
        Schema::dropIfExists('assigned_employees');
    }
};
