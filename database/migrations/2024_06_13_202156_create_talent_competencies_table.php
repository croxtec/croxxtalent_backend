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
        Schema::create('talent_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('cv_id');
            $table->string('competency');
            $table->string('level');
            $table->integer('match_percentage')->nullable();
            $table->integer('benchmark')->nullable();
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
        Schema::dropIfExists('talent_competencies');
    }
};
