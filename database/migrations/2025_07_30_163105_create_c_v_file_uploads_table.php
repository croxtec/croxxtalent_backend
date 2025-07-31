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
        Schema::create('cv_file_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cv_id')->nullable();
            $table->string('file_name');
            $table->string('original_name');
            $table->unsignedBigInteger('file_size');
            $table->string('file_url');
            $table->string('file_type');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cv_id')->references('id')->on('cvs')->onDelete('cascade');

            $table->index(['user_id', 'is_primary']);
            $table->index('uploaded_at');
        });

        Schema::table('cvs', function (Blueprint $table) {
            $table->string('cv_file_url')->nullable()->after('country_code');
            $table->boolean('is_active')->default(true)->after('cv_file_url');
        });

        // Add column to applied_jobs table
        Schema::table('applied_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('cv_upload_id')->nullable()->after('talent_cv_id');
            $table->foreign('cv_upload_id')->references('id')->on('cv_file_uploads');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_v_file_uploads');

        // Remove columns from cvs table
        Schema::table('cvs', function (Blueprint $table) {
            $table->dropColumn(['cv_file_url', 'is_active']);
        });

        // Remove column & foreign key from applied_jobs table
        Schema::table('applied_jobs', function (Blueprint $table) {
            $table->dropForeign(['cv_upload_id']);
            $table->dropColumn('cv_upload_id');
        });
    }
};
