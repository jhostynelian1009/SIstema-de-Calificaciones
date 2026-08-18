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
        Schema::create('partials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->onDelete('cascade');
            $table->unsignedTinyInteger('number');
            $table->string('name', 50);
            $table->decimal('weight', 5, 2)->default(50.00);
            $table->timestamps();

            $table->unique(['academic_period_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partials');
    }
};
