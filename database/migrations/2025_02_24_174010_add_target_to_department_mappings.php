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
        Schema::table('department_mappings', function (Blueprint $table) {
            // $table->string('level')->after('competency')->default('beginner');
            $table->integer('target_score')->after('level')->default(70);
        });

        // Add target_value to department_setups
        Schema::table('department_setups', function (Blueprint $table) {
            $table->integer('target_score')->after('level')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop columns from department_mappings
        Schema::table('department_mappings', function (Blueprint $table) {
            $table->dropColumn(['level', 'target_score']);
        });

        // Drop target_value from department_setups
        Schema::table('department_setups', function (Blueprint $table) {
            $table->dropColumn('target_value');
        });
    }
};
