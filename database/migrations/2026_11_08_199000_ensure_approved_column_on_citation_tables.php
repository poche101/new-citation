<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The app reads and writes `approved` on both citation tables (approval toggle,
 * dashboard stats), but no earlier migration creates it. Databases that already
 * have the column are left untouched; a fresh install gets it here.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['department_citations', 'group_citations'] as $table) {
            if (! Schema::hasColumn($table, 'approved')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->boolean('approved')->default(false);
                });
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: this migration only repairs a missing column,
        // and on existing databases the column pre-dates it.
    }
};
