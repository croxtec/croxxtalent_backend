<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_user_id');
            $table->foreignId('talent_user_id');
            $table->foreignId('talent_cv_id');
            $table->foreignId('campaign_id')->nullable();
            $table->string('status', 50)->default('pending'); // pending | accepted | rejected
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('employed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('employer_comment')->nullable();
            $table->text('talent_comment')->nullable();
            $table->timestamps();

            $table->foreign('employer_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('talent_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('talent_cv_id')->references('id')->on('cvs')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_invitations');
    }
}
