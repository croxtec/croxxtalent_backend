<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCvsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('industry_id')->nullable();
            $table->foreignId('job_title_id')->nullable();
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            $table->string('other_name', 30)->nullable();
            $table->string('gender')->nullable(); // male, female,
            $table->date('date_of_birth')->nullable();
            $table->string('class')->nullable(); // executive, professional, technician
            $table->text('career_summary')->nullable();
            $table->text('photo')->nullable();
            $table->string('email');
            $table->string('phone', 25)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->foreignId('state_id')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_title_id')->references('id')->on('job_titles')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('country_code')->references('code')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cvs');
    }
}
