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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->onDelete('restrict');
            $table->foreignId('partial_id')->constrained('partials')->onDelete('restrict');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('percentage', 5, 2);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();

            // Custom short index names for MariaDB compatibility (< 64 chars)
            $table->index(['teaching_assignment_id', 'partial_id', 'active'], 'idx_act_ta_part_act');
            $table->index(['partial_id', 'active'], 'idx_act_part_act');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
