<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('employee_id');
            $table->date('date');

            // Time Summary
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->integer('break_minutes')->default(0);

            // Work Summary
            $table->decimal('billable_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            // Status Tracking
            $table->enum('attendance_status', ['present', 'absent', 'on_leave', 'holiday'])->default('present');
            $table->enum('shift_status', ['regular', 'overtime', 'off', 'rest_day'])->default('regular');

            // Relationships (Optional)
            $table->foreignId('shift_id')->nullable();
            $table->foreignId('employee_shift_id')->nullable();// For advanced module
            $table->foreignId('attendance_id')->nullable();

            // JSON fields for flexible reporting
            $table->json('timesheet_summary')->nullable();  // Store daily timesheet entries
            $table->json('meta_data')->nullable();         // Additional company-specific data

            $table->timestamps();
            $table->index(['company_id', 'employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_reports');
    }
};
