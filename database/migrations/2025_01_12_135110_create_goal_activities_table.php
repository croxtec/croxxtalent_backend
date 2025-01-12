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
        Schema::create('goal_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('project_goals')->cascadeOnDelete();
            $table->string('activity_type'); // Type of activity (e.g., review, update)
            $table->text('description')->nullable(); // Description of the activity
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete(); // User who performed the activity
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
        Schema::dropIfExists('goal_activities');
    }
};
