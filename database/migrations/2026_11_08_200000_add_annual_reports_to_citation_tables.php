<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the annual report fields and the unit/department citation to both
 * submission tables. Columns are nullable so existing submissions stay valid;
 * the forms make them required for every new submission.
 *
 * The existing citation column (department_citations.citation and
 * group_citations.description) is the PERSONAL citation and is left untouched.
 */
return new class extends Migration
{
    private array $tables = ['department_citations', 'group_citations'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->text('unit_citation')->nullable();
                $t->text('personal_report_summary')->nullable();
                $t->longText('personal_report_detail')->nullable();
                $t->text('unit_report_summary')->nullable();
                $t->longText('unit_report_detail')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn([
                    'unit_citation',
                    'personal_report_summary',
                    'personal_report_detail',
                    'unit_report_summary',
                    'unit_report_detail',
                ]);
            });
        }
    }
};
