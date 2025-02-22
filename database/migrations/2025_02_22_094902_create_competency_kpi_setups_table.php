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
        Schema::create('competency_kpi_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_mapping_id')->constrained('department_mappings')->onDelete('cascade');
            $table->string('kpi_name');
            $table->text('description');
            $table->enum('level', ['Beginner', 'Intermediate', 'Advance', 'Expert']);
            $table->string('frequency');
            $table->integer('target_score');
            $table->integer('weight');
            $table->timestamps();

            $table->unique(['department_mapping_id', 'kpi_name', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competency_kpi_setups');
    }
};
