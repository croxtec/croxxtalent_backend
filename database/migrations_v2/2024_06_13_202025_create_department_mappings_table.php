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
        Schema::create('department_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id');
            $table->foreignId('department_id');
            $table->string('competency');
            $table->enum('competency_role', ['technical_skill', 'soft_skill']);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('department_mappings');
    }
};
