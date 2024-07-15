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
        Schema::create('croxx_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id');
            $table->string('title');
            $table->string('alias');
            $table->longText('description');
            $table->text('video_url')->nullable();
            $table->text('pdf')->nullable();

            $table->integer('order')->default(1);
            $table->json('resources')->nullable();
            $table->string('keyword')->nullable();
            $table->string('cover_photo')->nullable();
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
        Schema::dropIfExists('croxx_lessons');
    }
};
