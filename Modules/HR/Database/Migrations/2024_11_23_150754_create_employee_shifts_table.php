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
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('shift_id');
            $table->date('date');
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'absent'])->default('scheduled');
            $table->text('notes')->nullable();
            // Overtime
            $table->integer('overtime_minutes')->default(0);
            $table->decimal('overtime_rate', 3, 2)->default(1.5);
            $table->boolean('is_overtime_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('employees');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
    }
};
