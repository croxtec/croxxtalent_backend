<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_user_id');
            $table->foreignId('talent_user_id');
            $table->foreignId('talent_cv_id');
            $table->timestamps();

            $table->foreign('affiliate_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('talent_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('talent_cv_id')->references('id')->on('cvs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliates');
    }
}
