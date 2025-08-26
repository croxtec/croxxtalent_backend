<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::table('competency_setups', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });

        Schema::table('croxx_assessments', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });

        Schema::table('evaluation_question_banks', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });

        Schema::table('department_setups', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });

        Schema::table('department_mappings', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });

        Schema::table('croxx_trainings', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });

        Schema::table('croxx_lessons', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });

        Schema::table('lesson_setups', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('competency_setups', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('croxx_assessments', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('evaluation_question_banks', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('department_setups', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('department_mappings', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('croxx_trainings', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('croxx_lessons', function (Blueprint $table) {
            $table->dropColumn('language');
        });

         Schema::table('lesson_setups', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
