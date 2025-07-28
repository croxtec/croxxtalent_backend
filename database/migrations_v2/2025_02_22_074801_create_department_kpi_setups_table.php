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
        Schema::create('department_kpi_setups', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->json('department_goals')->nullable();
            $table->json('level_kpis')->nullable();
            $table->json('recommended_assessments')->nullable();
            $table->json('recommended_trainings')->nullable();
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
        Schema::dropIfExists('department_kpi_setups');
    }
};
