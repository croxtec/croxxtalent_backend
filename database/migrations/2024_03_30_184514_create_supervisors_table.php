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
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id');
            $table->foreignId('supervisor_id');
            $table->string('type');
            $table->foreignId('department_id')->nullable();
            $table->foreignId('department_role_id')->nullable();
            $table->foreignId('employee_id')->nullable();
            $table->string('email')->nullable(); //extrnal supervisor
            $table->json('employees')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supervisors');
    }
};
