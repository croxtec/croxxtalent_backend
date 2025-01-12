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
        Schema::create('project_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('employer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable();
            $table->string('title');
            $table->longText('metric')->nullable(); // KPIs or progress metric
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('priority_level', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['to-do', 'in-progress', 'in-review','rework', 'completed' ])->default('to-do');
            // $table->json('employees_assigned')->nullable(); // Array of assigned user IDs
            $table->decimal('rating', 5, 2)->nullable();
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
        Schema::dropIfExists('project_goals');
    }
};
