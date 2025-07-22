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
        Schema::create('department_setups', function (Blueprint $table) {
            $table->id();
            $table->string('department')->nullable();
            $table->string('competency');
            $table->string('level')->nullable();
            $table->string('department_role')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('generated_id')->nullable();
            $table->enum('status', ['draft', 'publish'])->default('publish');
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
        Schema::dropIfExists('department_setups');
    }
};
