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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Links to employees table
            $table->date('date')->index(); // The date of attendance
            $table->time('check_in')->nullable(); // Check-in time
            $table->time('check_out')->nullable(); // Check-out time
            $table->enum('status', ['present', 'absent', 'on_leave', 'holiday'])->default('present'); // Attendance status
            $table->text('notes')->nullable(); // Optional notes for the day
            $table->integer('late_minutes')->default(0);

            $table->boolean('is_late')->default(false);
             // Optional relationships - nullable
            $table->foreignId('shift_id')->nullable();
            $table->foreignId('employee_shift_id')->nullable();// For advanced module
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
