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
        Schema::create('employer_jobcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id');
            $table->string('job_code');
            $table->string('job_title')->nullable();
            $table->text('description')->nullable();
            $table->json('managers')->nullable();
            $table->foreignId('manager1_id')->nullable();
            $table->foreignId('manager2_id')->nullable();
            $table->foreignId('manager3_id')->nullable();
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
        Schema::dropIfExists('employer_jobcodes');
    }
};
