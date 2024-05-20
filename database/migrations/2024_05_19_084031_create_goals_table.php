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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('employee_id')->nullable(); //supervisor, or created for
            $table->foreignId('supervisor_id')->nullable(); //supervisor, or created for
            $table->foreignId('employer_id')->nullable();

            $table->string('title');
            $table->dateTime('period');
            $table->string('reminder');
            $table->text('metric')->nullable();
            $table->enum('status', ['pending', 'done', 'missed'])->default('pending');
            $table->integer('score')->nullable();
            $table->timestamp('archived_at')->nullable();
            // $table->boolean('is_published')->default(false);
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
        Schema::dropIfExists('goals');
    }
};
