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
        Schema::table('cv_skills', function (Blueprint $table) {
            $table->foreignId('domain_id');
            $table->foreignId('core_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cv_skills', function (Blueprint $table) {
            $table->dropColumn(['domain_id', 'core_id']);
        });
    }
};
