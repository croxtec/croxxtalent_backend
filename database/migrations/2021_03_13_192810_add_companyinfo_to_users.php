<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyinfoToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_size')->nullable();
            $table->text('company_affiliate')->nullable();
            $table->string('services')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */ 
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('company_size');
            $table->dropColumn('company_affiliate');
            $table->dropColumn('services');
        });
    }
}
