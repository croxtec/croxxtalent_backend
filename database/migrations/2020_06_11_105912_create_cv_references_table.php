<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCvReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id');
            $table->string('name', 50);
            $table->string('company', 100);
            $table->string('position', 50);
            $table->string('email');
            $table->string('phone', 25)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->foreign('cv_id')->references('id')->on('cvs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cv_references');
    }
}
