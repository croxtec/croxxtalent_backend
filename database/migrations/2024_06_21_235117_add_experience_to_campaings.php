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
        Schema::table('campaings', function (Blueprint $table) {
             $table->string('experience_level')->nullable();
             $table->string('work_site')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaings', function (Blueprint $table) {
            $table->dropColumne(['experience_level', 'work_site']);
        });
    }
};
