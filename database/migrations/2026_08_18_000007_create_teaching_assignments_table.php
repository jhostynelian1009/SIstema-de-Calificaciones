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
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('course_id')->constrained('courses')->onDelete('restrict');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('restrict');
            $table->foreignId('academic_period_id')->constrained('academic_periods')->onDelete('restrict');
            $table->boolean('active')->default(true)->index();
            $table->timestamps();

            $table->unique(['course_id', 'subject_id', 'academic_period_id'], 'teaching_assignments_csp_unique');
            $table->index(['teacher_id', 'academic_period_id', 'active']);
            $table->index(['course_id', 'academic_period_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
