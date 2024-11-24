<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('shift_id')->nullable();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Links to employees table
            $table->foreignId('project_id')->nullable(); // Links to projects table
            $table->date('date'); // Date of the work log
            $table->string('task_description'); // Description of the task
            $table->time('start_time')->nullable(); // Task start time
            $table->time('end_time')->nullable(); // Task end time
            $table->decimal('hours_spent', 8, 2)->nullable(); // Total hours spent (calculated or manual entry)
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('approved_by')->nullable();
            $table->text('file')->nullable(); // Additional notes for the task
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
