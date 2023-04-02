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
        Schema::create('assesments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id');
            $table->foreignId('employer_id');
            $table->foreignId('domain_id');
            $table->foreignId('core_id');
            $table->foreignId('skill_id');
            $table->string('level');
            $table->string('code')->nullable();

            $table->string('type')->default('experience'); //experience,vetting
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('validity_period')->nullable();
            $table->string('delivery_type')->nullable();
            $table->integer('expected_score')->nullable();

            $table->json('candidate')->nullable();
            $table->json('manager')->nullable();

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
        Schema::dropIfExists('assesments');
    }
};
