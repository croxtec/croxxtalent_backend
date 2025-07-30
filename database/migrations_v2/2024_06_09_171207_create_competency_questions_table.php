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
        Schema::create('competency_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id');
            $table->enum('state', ['bank', 'publish'])->default('publish');
            $table->text('question');
            $table->text('description')->nullable();
            $table->integer('order')->default(1);
            $table->json('files')->nullable();
            $table->integer('duration')->nullable();
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
        Schema::dropIfExists('competency_questions');
    }
};
