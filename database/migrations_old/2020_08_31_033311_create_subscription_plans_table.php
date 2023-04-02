<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // employer
            $table->string('name', 100);
            $table->string('interval', 100); // daily, weekly, monthly, quarterly and yearly. annually on PAYSTACK | yearly on FLUTTERWAVE
            $table->integer('duration'); // 1, 3, 6, 12
            $table->char('currency_code', 3);
            $table->double('amount', 20, 8)->default(0.0);
            $table->integer('discount_percentage')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('subscription_plans');
    }
}
