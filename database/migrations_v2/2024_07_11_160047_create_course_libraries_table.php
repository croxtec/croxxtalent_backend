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
        Schema::create('course_libraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_id');
            $table->foreignId('training_id')->nullable();
            $table->integer('progress')->default(0);
            $table->integer('current_lesson')->default(1);
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
        Schema::dropIfExists('course_libraries');
    }
};
