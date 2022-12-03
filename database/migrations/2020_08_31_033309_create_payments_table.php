<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 100)->unique();
            $table->string('gateway', 100)->nullable();
            $table->morphs('payable');
			$table->foreignId('user_id')->nullable();
			$table->text('user_data')->nullable();
			$table->char('currency_code', 3);
			$table->double('amount', 20, 8)->default(0.00);
			$table->double('charges', 20, 8)->default(0.00);
			$table->double('total', 20, 8)->default(0.00);
			$table->string('description', 255);
			$table->text('gateway_response')->nullable();
			$table->string('status', 20)->nullable();
			$table->string('status_description', 255)->nullable();
			$table->boolean('is_processed')->default(0);
			$table->boolean('is_value_given')->default(0);
            $table->string('value_given_status', 50)->nullable();
            $table->timestamps();
            $table->ipAddress('created_at_ip');
            $table->ipAddress('updated_at_ip')->nullable();
			$table->index(['is_processed', 'created_at']);
        });
    }
 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
