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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('employer_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->morphs('mediable'); // Creates mediable_type and mediable_id
            $table->string('collection_name')->default('default');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->string('file_url');
            $table->string('cloudinary_public_id');
            $table->json('metadata')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('employer_id')->references('id')->on('users');
            $table->foreign('employee_id')->references('id')->on('employees');
            
            // Indexes for better performance
            $table->index(['employer_id', 'created_at']);
            $table->index(['employee_id', 'created_at']);
            $table->index('collection_name');
            // $table->index(['mediable_type', 'mediable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
};
