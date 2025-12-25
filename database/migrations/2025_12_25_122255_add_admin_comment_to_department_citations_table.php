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
    Schema::table('department_citations', function (Blueprint $table) {
        $table->text('admin_comment')->nullable()->after('approved');
    });
}

public function down(): void
{
    Schema::table('department_citations', function (Blueprint $table) {
        $table->dropColumn('admin_comment');
    });
}

};
