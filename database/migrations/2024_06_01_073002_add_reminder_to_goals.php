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
        Schema::table('goals', function (Blueprint $table) {
            $table->string('type')->default('career');
            $table->foreignId('department_id')->nullable();
            $table->foreignId('role_id')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->dateTime('reminder_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumne(['type','department_id', 'role_id', 'parent_id', 'reminder_date']);
        });
    }
};
