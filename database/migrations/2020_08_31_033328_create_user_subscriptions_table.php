<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('subscription_plan_id');
            $table->foreignId('payment_id')->nullable();
            $table->char('currency_code', 3)->nullable();
            $table->double('amount', 20, 8);
            $table->timestamp('start_at')->useCurrent();
            $table->timestamp('end_at')->useCurrent();
            $table->timestamp('renew_at')->nullable();
            $table->integer('renew_attempt')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
}
