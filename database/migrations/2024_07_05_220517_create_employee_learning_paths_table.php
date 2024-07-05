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
        Schema::create('employee_learning_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_user_id');
            $table->foreignId('employee_id');
            $table->foreignId('assessment_feedback_id')->nullable();

            $table->foreignId('training_id')->nullable();
            $table->integer('progress')->default(0);
            $table->timestamp('archived_at')->nullable();
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
        Schema::dropIfExists('employee_learning_paths');
    }
};
