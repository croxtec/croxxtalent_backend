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
        Schema::create('goal_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('project_goals')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('goal_comments')->cascadeOnDelete();
            $table->text('comment');
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goal_comments');
    }
};
