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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('domain_id')->nullable();
            $table->foreignId('core_id')->nullable();
            $table->string('interview')->default('no_interview')->nullable(); // no interview, croxxtalent interview ,external interview
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['domain_id', 'core_id', 'interview']);
        });
    }
};
