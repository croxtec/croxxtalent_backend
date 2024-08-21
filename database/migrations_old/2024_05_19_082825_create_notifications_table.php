<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('notifiable_type')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('category')->default('primary');
            $table->timestamps();
        });
        // $table->id();
        // $table->foreignId('user_id');
        // $table->string('action')->nullable();
        // $table->string('title')->nullable();
        // $table->string('message')->nullable();
        // $table->boolean('seen')->default(0);
        // $table->string('category')->default('primary');
        // $table->dateTime('received_at')->nullable()->default(now());
        // $table->timestamps();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
