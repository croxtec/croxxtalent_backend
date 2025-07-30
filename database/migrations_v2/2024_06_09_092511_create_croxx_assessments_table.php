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
        Schema::create('croxx_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('employer_id');
            $table->string('code');
            $table->string('level');
            $table->string('category');
            $table->string('type')->default('company');

            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('validity_period')->nullable();
            $table->text('delivery_type')->nullable();
            $table->integer('expected_percentage')->nullable();

            $table->foreignId('department_id')->nullable();
            $table->foreignId('career_id')->nullable();
            $table->foreignId('department_role_id')->nullable();
            $table->json('roles')->nullable();
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
        Schema::dropIfExists('croxx_assessments');
    }
};
