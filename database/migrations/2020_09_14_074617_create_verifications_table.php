<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('verifiable');
            $table->string('action', 50);   
            $table->string('token', 191);         
            $table->string('sent_to', 191);
            $table->boolean('is_otp')->default(false);
			$table->text('metadata')->nullable();
            $table->timestamps();			
			
			$table->index(['action', 'sent_to']);
			$table->index('token');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verifications');
    }
}
