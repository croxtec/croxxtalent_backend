<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->char('country_code', 2);
			$table->string('name', 100);
			$table->double('latitude', 10, 7)->nullable()->default(0.0);
			$table->double('longitude', 10, 7)->nullable()->default(0.0);
            $table->double('altitude', 5, 1)->nullable()->default(0.0);
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();
            
            $table->foreign('country_code')->references('code')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('states');
    }
}
