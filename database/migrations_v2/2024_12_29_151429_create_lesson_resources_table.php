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
        Schema::create('lesson_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_user_id')->nullable();
            $table->foreignId('training_id');
            $table->foreignId('lesson_id');
            $table->string('title');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->double('file_size')->nullable();
            $table->string('file_url')->nullable();
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
        Schema::dropIfExists('lesson_resources');
    }
};
