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
        Schema::create('croxx_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('employer_id');
            $table->string('code');
            $table->enum('type', ['company', 'competency', 'training'])->default('company'); //competency

            $table->string('title');
            $table->string('experience_level');
            $table->longText('objective')->nullable();
            $table->string('assessment_level')->nullable();
            $table->boolean('is_free')->default(true);
            $table->decimal('price', 8, 2)->default(0);
            $table->string('currency')->nullable();
            $table->integer('ratings')->default(0);

            $table->json('tags')->nullable();
            $table->string('cover_photo')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('career_id')->nullable();
            $table->foreignId('assessment_id')->nullable();
            $table->boolean('is_published')->default(false);
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
        Schema::dropIfExists('croxx_trainings');
    }
};
