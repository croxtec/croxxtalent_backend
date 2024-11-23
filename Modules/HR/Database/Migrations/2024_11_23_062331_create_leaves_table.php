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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index();
            $table->foreignId('employee_id')->index();
            $table->foreignId('leave_type_id')->index();
            $table->string('duration')->nullable();
            $table->date('leave_date')->nullable();
            $table->text('reason');
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');

            $table->foreignId('added_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->date('approved_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->text('approved_reason')->nullable();

            $table->foreignId('shift_id')->nullable(); // If they're missing a specific shift
            $table->time('start_time')->nullable();    // For partial day leaves
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
