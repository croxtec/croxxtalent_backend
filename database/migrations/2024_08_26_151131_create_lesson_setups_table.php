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
        Schema::create('lesson_setups', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->string('level');
            $table->string('title')->nullable();
            $table->string('alias')->nullable();
            $table->text('description')->nullable();
            $table->json('keywords')->nullable();
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
        Schema::dropIfExists('lesson_setups');
    }
};
