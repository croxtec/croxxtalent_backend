<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();            
            $table->string('type'); // talent, employer, affiliate, admin
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            $table->string('company_name', 100)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('email_updated_at')->nullable();
            $table->string('password');
            $table->timestamp('password_updated_at')->nullable();
            $table->rememberToken();
            $table->foreignId('role_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('photo')->nullable();
            $table->foreignId('referral_user_id')->nullable();
            $table->string('referral_code', 100)->nullable();
            $table->integer('affiliate_reward_points')->default(0);
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('referral_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
