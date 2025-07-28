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
        Schema::table('goals', function (Blueprint $table) {
            // Add new status fields
            $table->enum('employee_status', ['done', 'missed'])->nullable()->after('status');
            $table->enum('supervisor_status', ['done', 'missed'])->nullable()->after('employee_status');
            
            // Add comment fields
            $table->text('employee_comment')->nullable()->after('supervisor_status');
            $table->text('supervisor_comment')->nullable()->after('employee_comment');
            
            // Add timestamp fields
            $table->timestamp('employee_submitted_at')->nullable()->after('supervisor_comment');
            $table->timestamp('supervisor_reviewed_at')->nullable()->after('employee_submitted_at');
        });

        // Update existing status enum to include new values
        DB::statement("ALTER TABLE goals MODIFY COLUMN status ENUM('pending', 'employee_submitted', 'supervisor_review', 'done', 'missed', 'rejected')");
    }

    public function down()
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn([
                'employee_status',
                'supervisor_status', 
                'employee_comment',
                'supervisor_comment',
                'employee_submitted_at',
                'supervisor_reviewed_at'
            ]);
        });

        // Revert status enum to original values
        DB::statement("ALTER TABLE goals MODIFY COLUMN status ENUM('pending', 'done', 'missed')");
    }
};
