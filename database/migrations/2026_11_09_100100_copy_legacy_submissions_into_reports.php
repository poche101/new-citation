<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copies submissions made with the old forms (department_citations and
 * group_citations) into the new `reports` table, so nothing disappears from
 * the admin area.
 *
 * - One old row can become several reports (personal citation, unit citation,
 *   personal report, unit report), depending on which parts were filled in.
 * - Uploaded files follow the first report created from their old row.
 * - The old tables are NOT changed or dropped. Nothing here deletes data.
 * - It only runs while `reports` is empty, so running it twice is harmless.
 */
return new class extends Migration
{
    private const REPORT_MODEL = 'App\\Models\\Report';

    public function up(): void
    {
        if (! Schema::hasTable('reports') || DB::table('reports')->exists()) {
            return;
        }

        DB::transaction(function () {
            $this->copy('department_citations', 'App\\Models\\DepartmentCitation', 'department');
            $this->copy('group_citations', 'App\\Models\\GroupCitation', 'group');
        });
    }

    public function down(): void
    {
        // Intentionally empty: the old tables were never modified, and
        // dropping `reports` is handled by the migration that created it.
    }

    private function copy(string $table, string $legacyModel, string $entityType): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        // department / group name -> departments.id
        $entityIds = Schema::hasTable('departments')
            ? DB::table('departments')->where('type', $entityType)->pluck('id', 'name')
            : collect();

        DB::table($table)->orderBy('id')->each(function ($row) use ($legacyModel, $entityType, $entityIds) {
            $r = (array) $row;
            $isGroup = $entityType === 'group';

            $entityName = $this->clean($isGroup ? ($r['group_name'] ?? null) : ($r['department'] ?? null));
            [$from, $to] = $isGroup
                ? [$this->date($r['period_from'] ?? null), $this->date($r['period_to'] ?? null)]
                : $this->parsePeriod($r['period'] ?? null);

            $base = [
                'title'         => $this->clean($r['title'] ?? null),
                'name'          => $this->clean($isGroup ? ($r['name'] ?? null) : ($r['fullname'] ?? null)) ?? 'Unknown',
                'unit'          => $this->clean($r['unit'] ?? null) ?? '',
                'designation'   => $this->clean($r['designation'] ?? null) ?? '',
                'kingschat'     => $this->clean($r['kingschat'] ?? null) ?? '',
                'phone'         => $this->clean($r['phone'] ?? null) ?? '',
                'entity_type'   => $entityType,
                'entity_name'   => $entityName ?? 'Unknown',
                'entity_id'     => $entityIds[$entityName] ?? null,
                'period_from'   => $from,
                'period_to'     => $to,
                'approved'      => (bool) ($r['approved'] ?? false),
                'admin_comment' => $this->clean($r['admin_comment'] ?? null),
                'created_at'    => $r['created_at'] ?? now(),
                'updated_at'    => $r['updated_at'] ?? now(),
            ];

            $personalCitation = $this->clean($isGroup ? ($r['description'] ?? null) : ($r['citation'] ?? null));

            $reports = [
                ['personal_citation', ['citation' => $personalCitation]],
                ['unit_citation', ['citation' => $this->clean($r['unit_citation'] ?? null)]],
                ['personal_report', [
                    'executive_summary' => $this->clean($r['personal_report_summary'] ?? null),
                    'detailed_report'   => $this->clean($r['personal_report_detail'] ?? null),
                ]],
                ['unit_report', [
                    'executive_summary' => $this->clean($r['unit_report_summary'] ?? null),
                    'detailed_report'   => $this->clean($r['unit_report_detail'] ?? null),
                ]],
            ];

            $firstId = null;

            foreach ($reports as [$type, $content]) {
                if (! array_filter($content)) {
                    continue; // that part was never filled in
                }

                $id = DB::table('reports')->insertGetId($base + ['type' => $type] + $content);
                $firstId ??= $id;
            }

            // Move uploaded files to the first report made from this row
            if ($firstId && Schema::hasTable('citation_attachments')) {
                DB::table('citation_attachments')
                    ->where('attachable_type', $legacyModel)
                    ->where('attachable_id', $row->id)
                    ->update(['attachable_type' => self::REPORT_MODEL, 'attachable_id' => $firstId]);
            }
        });
    }

    private function clean(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return ($value === null || $value === '') ? null : (string) $value;
    }

    private function date(mixed $value): ?string
    {
        try {
            return $value ? Carbon::parse($value)->toDateString() : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** Old forms stored the period as text such as "January 1, 2026 to October 1, 2026". */
    private function parsePeriod(?string $period): array
    {
        if (! $period) {
            return [null, null];
        }

        $parts = preg_split('/\s*(?:–|—|\bto\b)\s*/u', $period, 2);

        if (count($parts) === 2) {
            return [$this->date($parts[0]), $this->date($parts[1])];
        }

        $single = $this->date($parts[0]);

        return [$single, $single];
    }
};
