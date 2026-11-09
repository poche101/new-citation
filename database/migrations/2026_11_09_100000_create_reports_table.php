<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One table for everything people submit. `type` says which of the four it is:
 *   personal_report | unit_report | personal_citation | unit_citation
 *
 * Reports use executive_summary + detailed_report; citations use citation.
 * The department or group is stored by name (entity_name) so a later rename
 * never rewrites history; entity_id points at the departments table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();

            // Who submitted it
            $table->string('title')->nullable();
            $table->string('name');
            $table->string('unit');
            $table->string('designation');
            $table->string('kingschat');
            $table->string('phone', 30);

            // Which department or group it belongs to
            $table->string('entity_type', 20)->index();   // department | group
            $table->string('entity_name');
            $table->unsignedBigInteger('entity_id')->nullable();

            // Reporting period
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();

            // Content
            $table->text('executive_summary')->nullable();
            $table->longText('detailed_report')->nullable();
            $table->text('citation')->nullable();

            // Admin review
            $table->boolean('approved')->default(false);
            $table->text('admin_comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
