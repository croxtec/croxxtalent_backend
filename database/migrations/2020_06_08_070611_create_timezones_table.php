<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimezonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timezones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->char('country_code', 2)->nullable();
			$table->string('city', 255)->nullable();
			$table->string('offset', 20)->nullable();
			$table->string('gmt', 20)->nullable();
            $table->string('abbreviation', 20)->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();

			$table->index('name');
			$table->foreign('country_code')->references('code')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timezones');
    }
}
