<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title', 100);
            $table->foreignId('job_title_id');
            $table->foreignId('industry_id');
            $table->foreignId('minimum_degree_id');
            $table->string('job_title')->nullable();
            $table->string('work_type'); // contract, fulltime, parttime, internship
            $table->string('city', 255);
            $table->foreignId('state_id');
            $table->char('country_code', 2);
            $table->boolean('is_confidential_salary')->default(false);
            $table->char('currency_code', 3)->nullable();
            $table->double('min_salary', 20, 8)->nullable();
            $table->double('max_salary', 20, 8)->nullable();
            $table->integer('number_of_positions');
            $table->integer('years_of_experience');
            $table->text('summary');
            $table->text('description')->nullable();
            $table->text('photo')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_managed')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_title_id')->references('id')->on('job_titles');
            $table->foreign('industry_id')->references('id')->on('industries');
            $table->foreign('minimum_degree_id')->references('id')->on('degrees');
            $table->foreign('state_id')->references('id')->on('states');
            $table->foreign('country_code')->references('code')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
