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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->string('type_name');
            $table->integer('no_of_leaves')->default(1);
            $table->boolean('paid')->default(1);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('leavetype', ['monthly', 'yearly'])->default('yearly');
            $table->integer('yearly_limit')->default(1);
            $table->integer('effective_after')->nullable();
            $table->string('effective_type')->nullable();
            $table->string('unused_leave')->nullable();
            $table->boolean('encashed');
            $table->boolean('allowed_probation');
            $table->boolean('allowed_notice');
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
