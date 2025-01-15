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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable(); // Optional Supervisor
            $table->foreignId('department_id'); // References department
            $table->string('title');
            $table->string('code')->unique(); // Unique project code
            $table->text('description')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('project_type', ['internal', 'external'])->default('internal');
            $table->enum('priority_level', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('category')->nullable();
            $table->string('category')->nullable();
            $table->char('currency_code', 3);
            $table->decimal('budget', 15, 2)->default(0);
            $table->integer('resource_allocation')->default(0); // Resource count or percentage
            // $table->json('team_leads')->nullable(); // Array of team lead user IDs
            // $table->json('team_members')->nullable(); // Array of team member user IDs
            $table->decimal('overall_progress', 5, 2)->default(0); // Percent completion
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('projects');
    }
};
