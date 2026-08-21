<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->decimal('score', 4, 2);
            $table->string('observation', 500);
            $table->foreignId('graded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('graded_at');
            $table->timestamps();

            $table->unique(['activity_id', 'student_id'], 'grades_activity_student_unique');
            $table->index(['student_id', 'graded_at'], 'grades_student_graded_at_index');
            $table->index(['graded_by', 'graded_at'], 'grades_graded_by_graded_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
