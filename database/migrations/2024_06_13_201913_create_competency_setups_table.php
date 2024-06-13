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
        Schema::create('competency_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->nullable();
            $table->string('job_title');
            $table->string('competency');
            $table->string('level')->nullable();
            $table->integer('match_percentage')->nullable();
            $table->integer('benchmark')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('generated_id')->nullable();
            $table->enum('status', ['draft', 'publish'])->default('publish');
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
        Schema::dropIfExists('competency_setups');
    }
};
