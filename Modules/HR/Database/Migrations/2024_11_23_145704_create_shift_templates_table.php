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
        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_duration')->default(60); // in minutes
            $table->boolean('is_night_shift')->default(false);
            $table->integer('grace_period')->default(15); // in minutes
            $table->boolean('is_active')->default(true);
            $table->foreignId('company_id');  // Missing company isolation
            $table->string('recurring_days')->nullable(); // For M,T,W etc.
            $table->integer('repeat_frequency')->nullable(); // Weekly repeat
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_templates');
    }
};
