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
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_role_id')->nullable();
            $table->integer('status')->default(0);
            $table->string('level')->nullable();
            $table->foreignId('supervisor_id')->nullable();
            $table->timestamp('hired_date')->nullable();
            $table->text('location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['department_role_id', 'status','level','supervisor_id','hired_date', 'location']);
        });
    }
};
