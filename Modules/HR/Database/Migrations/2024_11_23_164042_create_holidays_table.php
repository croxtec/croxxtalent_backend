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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->string('holiday_name'); // Name of the holiday (e.g., Christmas)
            $table->date('holiday_date'); // Holiday date
            $table->enum('type', ['public', 'optional', 'restricted'])->default('public'); // Type of holiday
            $table->json('applicable_to')->nullable(); // Optional: Departments, designations, or employees
            $table->boolean('is_recurring')->default(false); // Recurring holiday (e.g., annual festivals)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
