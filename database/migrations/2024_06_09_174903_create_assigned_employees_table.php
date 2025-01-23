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
        Schema::create('assigned_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id');
            $table->foreignId('employee_id');
            $table->foreignId('employer_id')->nullable();
            $table->foreignId('talent_id')->nullable();
            $table->boolean('is_supervisor')->default(false);
            $table->json('meta')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
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
