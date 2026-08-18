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
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'entity_type')) {
                $table->renameColumn('entity_type', 'auditable_type');
            }
            if (Schema::hasColumn('audit_logs', 'entity_id')) {
                $table->renameColumn('entity_id', 'auditable_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->renameColumn('auditable_type', 'entity_type');
            }
            if (Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->renameColumn('auditable_id', 'entity_id');
            }
        });
    }
};
