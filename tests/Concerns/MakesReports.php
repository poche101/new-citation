<?php

namespace Tests\Concerns;

use App\Models\Admin;
use App\Models\Department;
use App\Models\Report;
use Illuminate\Http\UploadedFile;

trait MakesReports
{
    protected function words(int $n): string
    {
        return implode(' ', array_fill(0, $n, 'word'));
    }

    protected function department(string $name = 'Finance', string $type = 'department'): Department
    {
        return Department::create(['name' => $name, 'type' => $type]);
    }

    protected function admin(): Admin
    {
        return Admin::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('secret-pass')]);
    }

    protected function actingAsAdmin(): static
    {
        return $this->actingAs($this->admin(), 'admin');
    }

    /** Fields every form posts, plus the content fields of the given type. */
    protected function payload(string $type, array $overrides = []): array
    {
        $base = [
            'title'       => 'Bro',
            'name'        => 'John Doe',
            'unit'        => 'Ushers',
            'designation' => 'Leader',
            'kingschat'   => '@john',
            'phone'       => '0803 000 0000',
            'period_from' => '2026-01-01',
            'period_to'   => '2026-11-30',
        ];

        $content = str_ends_with($type, '_report')
            ? ['executive_summary' => 'A short summary.', 'detailed_report' => 'The long detailed report.']
            : ['citation' => 'A short citation.'];

        return array_merge($base, $content, $overrides);
    }

    protected function submitUrl(string $type, Department $department): string
    {
        return route('reports.store', [config("citation.reports.{$type}.slug"), $department->id]);
    }

    protected function makeReport(string $type = 'personal_citation', array $overrides = []): Report
    {
        return Report::create(array_merge([
            'type'        => $type,
            'title'       => 'Bro',
            'name'        => 'John Doe',
            'unit'        => 'Ushers',
            'designation' => 'Leader',
            'kingschat'   => '@john',
            'phone'       => '0803 000 0000',
            'entity_type' => 'department',
            'entity_name' => 'Finance',
            'period_from' => '2026-01-01',
            'period_to'   => '2026-11-30',
        ], str_ends_with($type, '_report')
            ? ['executive_summary' => 'Summary text', 'detailed_report' => 'Detail text']
            : ['citation' => 'Citation text'], $overrides));
    }

    protected function pdf(string $name = 'report.pdf', int $kb = 50): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kb, 'application/pdf');
    }
}
