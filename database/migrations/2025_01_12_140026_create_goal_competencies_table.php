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
        Schema::create('goal_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('project_goals')->cascadeOnDelete(); // References goal
            $table->foreignId('competency_id'); // References competency
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
        Schema::dropIfExists('goal_competencies');
    }
};
