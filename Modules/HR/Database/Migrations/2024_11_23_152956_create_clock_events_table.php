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
        Schema::create('clock_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('employee_id')->constrained('employees');
            $table->enum('event_type', ['clock_in', 'clock_out', 'break_start', 'break_end']);
            $table->dateTime('event_time');
             // Integration points
            $table->foreignId('shift_id')->nullable();
            $table->foreignId('employee_shift_id')->nullable();// For advanced module
            $table->foreignId('attendance_id')->nullable();

            $table->string('location')->nullable();
            $table->string('device_info')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('break_type', ['lunch', 'rest', 'other'])->nullable();
            $table->integer('break_duration')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clock_events');
    }
};
