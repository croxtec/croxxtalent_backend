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
        Schema::table('job_invitations', function (Blueprint $table) {
            $table->integer('score')->nullable();
            $table->dateTime('interview_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_invitations', function (Blueprint $table) {
            $table->dropColumn(['score', 'interview_at']);
        });
    }
};
