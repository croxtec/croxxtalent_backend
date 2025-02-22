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
        Schema::create('peer_review_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peer_review_id')->constrained()->onDelete('cascade');
            $table->foreignId('competency_id')->constrained('department_mappings')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('competency_questions')->onDelete('cascade');
            $table->text('answer')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            // Prevent duplicate feedback
            $table->unique(['peer_review_id', 'competency_id', 'question_id']);
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
        Schema::dropIfExists('peer_review_feedback');
    }
};
