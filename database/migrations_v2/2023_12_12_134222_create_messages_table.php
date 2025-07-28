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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('receiver_id');
            $table->integer('chat_id')->nullable();
            $table->integer('stage')->default(0);
            $table->longText('message')->nullable();
            $table->text('filepath')->nullable();
            $table->date('sent_at')->nullable();
            $table->date('read_at')->nullable();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
