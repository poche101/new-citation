<?php

namespace Tests\Feature;

use App\Models\CitationAttachment;
use App\Models\Department;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MakesReports;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use MakesReports, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /** Contents of a file download (CSV, Excel and Word come back as file responses). */
    private function downloaded(\Illuminate\Testing\TestResponse $response): string
    {
        $response->assertOk();

        return file_get_contents($response->baseResponse->getFile()->getPathname());
    }

    /** A report with one real uploaded file. */
    private function reportWithFile(string $type = 'personal_citation', array $overrides = []): Report
    {
        $report = $this->makeReport($type, $overrides);
        CitationAttachment::storeFor($report, $this->pdf('proof.pdf'));

        return $report->fresh('attachments');
    }

    /* ---------------- Access ---------------- */

    public function test_guests_are_sent_to_the_admin_login_not_an_error(): void
    {
        $report = $this->makeReport();

        foreach ([
            ['get', route('admin.reports.index')],
            ['get', route('admin.reports.edit', $report)],
            ['get', route('admin.reports.export', 'csv')],
            ['get', route('admin.dashboard')],
        ] as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('admin.login'));
        }

        $this->put(route('admin.reports.update', $report), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.reports.destroy', $report))->assertRedirect(route('admin.login'));

        $this->assertModelExists($report);
    }

    /* ---------------- Choosing which report to view ---------------- */

    public function test_dashboard_shows_a_card_per_report_type_with_counts_and_links(): void
    {
        $this->makeReport('personal_report');
        $this->makeReport('personal_report', ['name' => 'Second Person']);
        $this->makeReport('unit_citation');

        $response = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk();

        foreach (config('citation.reports') as $key => $type) {
            $response->assertSee($type['label'])
                ->assertSee(route('admin.reports.index', ['type' => $key]), false);
        }

        $response->assertSee('Submissions in total');
    }

    public function test_list_shows_everything_by_default_and_one_type_when_chosen(): void
    {
        $this->makeReport('personal_report', ['name' => 'Alpha Person']);
        $this->makeReport('unit_report', ['name' => 'Beta Person']);
        $this->makeReport('personal_citation', ['name' => 'Gamma Person']);
        $this->makeReport('unit_citation', ['name' => 'Delta Person']);
        $this->actingAsAdmin();

        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Alpha Person')->assertSee('Beta Person')->assertSee('Gamma Person')->assertSee('Delta Person');

        $this->get(route('admin.reports.index', ['type' => 'unit_report']))
            ->assertSee('Beta Person')
            ->assertDontSee('Alpha Person')->assertDontSee('Gamma Person')->assertDontSee('Delta Person');

        // An unknown type is ignored rather than breaking the page
        $this->get(route('admin.reports.index', ['type' => 'nonsense']))->assertOk()->assertSee('Alpha Person');
    }

    public function test_type_tabs_show_how_many_of_each_there_are(): void
    {
        $this->makeReport('personal_citation');
        $this->makeReport('personal_citation', ['name' => 'Another']);
        $this->makeReport('unit_report');

        $this->actingAsAdmin()->get(route('admin.reports.index'))
            ->assertSeeInOrder(['All reports', '3'], false);
    }

    public function test_search_and_status_filters_work_together_with_the_type(): void
    {
        $this->makeReport('personal_citation', ['name' => 'Paul Orife', 'approved' => true]);
        $this->makeReport('personal_citation', ['name' => 'Paul Pending', 'approved' => false]);
        $this->makeReport('unit_citation', ['name' => 'Paul Elsewhere']);
        $this->makeReport('personal_citation', ['name' => 'Someone Else']);
        $this->actingAsAdmin();

        $this->get(route('admin.reports.index', ['q' => 'Paul']))
            ->assertSee('Paul Orife')->assertSee('Paul Pending')->assertSee('Paul Elsewhere')->assertDontSee('Someone Else');

        $this->get(route('admin.reports.index', ['type' => 'personal_citation', 'q' => 'Paul', 'status' => 'approved']))
            ->assertSee('Paul Orife')->assertDontSee('Paul Pending')->assertDontSee('Paul Elsewhere');

        $this->get(route('admin.reports.index', ['q' => 'zzz-no-match']))
            ->assertSee('Nothing matches these filters');
    }

    public function test_search_also_matches_the_department_name(): void
    {
        $this->makeReport('personal_citation', ['name' => 'In Finance', 'entity_name' => 'Finance']);
        $this->makeReport('personal_citation', ['name' => 'In Music', 'entity_name' => 'Love World Music']);

        $this->actingAsAdmin()->get(route('admin.reports.index', ['q' => 'Music']))
            ->assertSee('In Music')->assertDontSee('In Finance');
    }

    public function test_the_list_page_has_the_delete_confirmation_and_edit_links(): void
    {
        $report = $this->makeReport();

        $this->actingAsAdmin()->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Delete this submission?')
            ->assertSee('This cannot be undone')
            ->assertSee(route('admin.reports.edit', $report), false);
    }

    /* ---------------- Editing ---------------- */

    public function test_edit_page_shows_the_fields_for_the_type(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.reports.edit', $this->makeReport('personal_report')))
            ->assertOk()
            ->assertSee('name="executive_summary"', false)
            ->assertSee('name="detailed_report"', false)
            ->assertDontSee('name="citation"', false);

        $this->get(route('admin.reports.edit', $this->makeReport('unit_citation')))
            ->assertOk()
            ->assertSee('name="citation"', false)
            ->assertDontSee('name="executive_summary"', false);
    }

    public function test_admin_can_edit_a_report(): void
    {
        $report = $this->makeReport('personal_report');

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_report', [
                'name'              => 'Corrected Name',
                'phone'             => '0900 111 2222',
                'period_from'       => '2026-02-01',
                'period_to'         => '2026-10-31',
                'executive_summary' => 'Updated summary',
                'detailed_report'   => 'Updated detail',
                'approved'          => '1',
                'admin_comment'     => 'Checked and fine',
            ]))
            ->assertRedirect(route('admin.reports.index', ['type' => 'personal_report']))
            ->assertSessionHas('success');

        $report->refresh();
        $this->assertSame('Corrected Name', $report->name);
        $this->assertSame('0900 111 2222', $report->phone);
        $this->assertSame('2026-02-01', $report->period_from->toDateString());
        $this->assertSame('Updated summary', $report->executive_summary);
        $this->assertSame('Updated detail', $report->detailed_report);
        $this->assertTrue($report->approved);
        $this->assertSame('Checked and fine', $report->admin_comment);
        $this->assertSame('personal_report', $report->type); // the type never changes
    }

    public function test_editing_enforces_the_same_limits_as_the_public_form(): void
    {
        $report = $this->makeReport('personal_citation');
        $this->actingAsAdmin();

        $this->put(route('admin.reports.update', $report), $this->payload('personal_citation', ['citation' => $this->words(151)]))
            ->assertSessionHasErrors('citation');

        $this->put(route('admin.reports.update', $report), $this->payload('personal_citation', ['period_from' => '2026-06-01', 'period_to' => '2026-01-01']))
            ->assertSessionHasErrors('period_to');

        $this->put(route('admin.reports.update', $report), $this->payload('personal_citation', ['name' => '']))
            ->assertSessionHasErrors('name');

        $this->assertSame('Citation text', $report->fresh()->citation);
    }

    public function test_unchecking_approved_marks_it_pending(): void
    {
        $report = $this->makeReport('personal_citation', ['approved' => true]);

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_citation')) // no "approved" field
            ->assertSessionHasNoErrors();

        $this->assertFalse($report->fresh()->approved);
    }

    public function test_admin_can_move_a_report_to_another_department_or_group(): void
    {
        $report = $this->makeReport('personal_citation', ['entity_name' => 'Finance']);
        $group = $this->department('Youth Group', 'group');

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_citation', ['entity_id' => $group->id]))
            ->assertSessionHasNoErrors();

        $report->refresh();
        $this->assertSame('Youth Group', $report->entity_name);
        $this->assertSame('group', $report->entity_type);
        $this->assertSame($group->id, $report->entity_id);
    }

    public function test_leaving_the_department_blank_keeps_the_current_one(): void
    {
        $report = $this->makeReport('personal_citation', ['entity_name' => 'Finance']);

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_citation', ['entity_id' => '']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Finance', $report->fresh()->entity_name);
    }

    public function test_admin_can_remove_a_file_and_it_leaves_the_disk(): void
    {
        $report = $this->reportWithFile();
        $file = $report->attachments->first();
        Storage::disk('local')->assertExists($file->path);

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_citation', ['remove_attachments' => [$file->id]]))
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($file);
        Storage::disk('local')->assertMissing($file->path);
    }

    public function test_admin_can_add_files_while_editing(): void
    {
        $report = $this->reportWithFile();

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_citation', [
                'attachments' => [UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4')],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $report->attachments()->count());
    }

    public function test_the_file_limit_counts_files_that_stay_attached(): void
    {
        $max = config('citation.uploads.max_files');
        $report = $this->makeReport();
        foreach (range(1, $max) as $i) {
            CitationAttachment::storeFor($report, $this->pdf("f{$i}.pdf"));
        }

        // Already full: one more is refused...
        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_citation', ['attachments' => [$this->pdf('extra.pdf')]]))
            ->assertSessionHasErrors('attachments');
        $this->assertSame($max, $report->attachments()->count());

        // ...but removing one in the same save makes room
        $remove = $report->attachments()->first();
        $this->put(route('admin.reports.update', $report), $this->payload('personal_citation', [
            'remove_attachments' => [$remove->id],
            'attachments'        => [$this->pdf('extra.pdf')],
        ]))->assertSessionHasNoErrors();
        $this->assertSame($max, $report->attachments()->count());
    }

    public function test_a_file_from_another_report_cannot_be_removed_through_this_one(): void
    {
        $mine = $this->makeReport();
        $other = $this->reportWithFile('unit_citation', ['name' => 'Someone Else']);
        $foreignFile = $other->attachments->first();

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $mine), $this->payload('personal_citation', ['remove_attachments' => [$foreignFile->id]]))
            ->assertSessionHasNoErrors();

        $this->assertModelExists($foreignFile);
        Storage::disk('local')->assertExists($foreignFile->path);
    }

    public function test_a_failed_edit_does_not_lose_files(): void
    {
        $report = $this->reportWithFile();
        $file = $report->attachments->first();

        $this->actingAsAdmin()
            ->put(route('admin.reports.update', $report), $this->payload('personal_citation', [
                'citation'           => $this->words(151), // invalid
                'remove_attachments' => [$file->id],
            ]))
            ->assertSessionHasErrors('citation');

        $this->assertModelExists($file);
        Storage::disk('local')->assertExists($file->path);
    }

    /* ---------------- Deleting ---------------- */

    public function test_admin_can_delete_a_report_and_its_files(): void
    {
        $report = $this->reportWithFile();
        $file = $report->attachments->first();

        $this->actingAsAdmin()
            ->delete(route('admin.reports.destroy', $report))
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($report);
        $this->assertModelMissing($file);
        Storage::disk('local')->assertMissing($file->path);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_deleting_only_removes_that_report(): void
    {
        $keep = $this->reportWithFile('unit_report', ['name' => 'Keep Me']);
        $remove = $this->makeReport('personal_citation');

        $this->actingAsAdmin()->delete(route('admin.reports.destroy', $remove));

        $this->assertModelExists($keep);
        Storage::disk('local')->assertExists($keep->attachments->first()->path);
    }

    public function test_after_deleting_the_admin_returns_to_the_filtered_list_they_came_from(): void
    {
        $report = $this->makeReport();
        $filtered = route('admin.reports.index', ['type' => 'personal_citation', 'q' => 'John']);

        $this->actingAsAdmin()
            ->from($filtered)
            ->delete(route('admin.reports.destroy', $report))
            ->assertRedirect($filtered);

        // Coming from the edit page (which no longer exists) goes to the list
        $again = $this->makeReport();
        $this->from(route('admin.reports.edit', $again))
            ->delete(route('admin.reports.destroy', $again))
            ->assertRedirect(route('admin.reports.index'));
    }

    public function test_deleting_a_missing_report_is_a_404(): void
    {
        $this->actingAsAdmin()->delete(route('admin.reports.destroy', 9999))->assertNotFound();
    }

    /* ---------------- Review ---------------- */

    public function test_admin_can_toggle_approval(): void
    {
        $report = $this->makeReport();
        $this->actingAsAdmin();

        $this->patch(route('admin.reports.approval', $report))->assertSessionHas('success');
        $this->assertTrue($report->fresh()->approved);

        $this->patch(route('admin.reports.approval', $report));
        $this->assertFalse($report->fresh()->approved);
    }

    public function test_admin_can_save_and_clear_a_comment(): void
    {
        $report = $this->makeReport();
        $this->actingAsAdmin();

        $this->postJson(route('admin.reports.comment', $report), ['comment' => 'Needs a date'])
            ->assertOk()->assertJson(['success' => true, 'comment' => 'Needs a date']);
        $this->assertSame('Needs a date', $report->fresh()->admin_comment);

        $this->postJson(route('admin.reports.comment', $report), ['comment' => null])->assertOk();
        $this->assertNull($report->fresh()->admin_comment);

        $this->postJson(route('admin.reports.comment', $report), ['comment' => str_repeat('a', 501)])
            ->assertUnprocessable();
    }

    /* ---------------- Exports ---------------- */

    public function test_csv_export_follows_the_chosen_type_and_filters(): void
    {
        $this->makeReport('personal_report', ['name' => 'Alpha Person']);
        $this->makeReport('unit_citation', ['name' => 'Beta Person']);
        $this->actingAsAdmin();

        $all = $this->downloaded($this->get(route('admin.reports.export', 'csv')));
        $this->assertStringContainsString('Alpha Person', $all);
        $this->assertStringContainsString('Beta Person', $all);
        $this->assertStringContainsString('Executive summary', $all);

        $onlyUnit = $this->downloaded($this->get(route('admin.reports.export', ['format' => 'csv', 'type' => 'unit_citation'])));
        $this->assertStringContainsString('Beta Person', $onlyUnit);
        $this->assertStringNotContainsString('Alpha Person', $onlyUnit);
    }

    public function test_exports_neutralise_spreadsheet_formulas_typed_by_visitors(): void
    {
        $this->makeReport('personal_citation', ['citation' => '=HYPERLINK("http://evil.example","click")']);

        $csv = $this->downloaded($this->actingAsAdmin()->get(route('admin.reports.export', 'csv')));

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertDoesNotMatchRegularExpression('/(^|,)"?=HYPERLINK/m', $csv);
    }

    public function test_every_export_format_downloads(): void
    {
        $this->reportWithFile('personal_report');
        $this->actingAsAdmin();

        foreach (['excel', 'csv', 'pdf', 'word'] as $format) {
            $this->get(route('admin.reports.export', $format))->assertOk();
        }

        $this->get(route('admin.reports.export', 'exe'))->assertNotFound();
    }

    /* ---------------- Departments page still works ---------------- */

    public function test_departments_page_counts_reports_and_a_rename_updates_them(): void
    {
        $dept = $this->department('Finance');
        $this->makeReport('personal_citation', ['entity_name' => 'Finance']);
        $this->makeReport('unit_report', ['entity_name' => 'Finance']);
        $this->makeReport('personal_citation', ['entity_name' => 'Finance', 'entity_type' => 'group']); // a group with the same name

        $this->actingAsAdmin()->get(route('admin.departments.index'))->assertOk();

        $this->put(route('admin.departments.update', $dept), ['name' => 'Finance and Accounts'])->assertSessionHasNoErrors();

        $this->assertSame(2, Report::where('entity_type', 'department')->where('entity_name', 'Finance and Accounts')->count());
        $this->assertSame(1, Report::where('entity_type', 'group')->where('entity_name', 'Finance')->count());
    }

    /* ---------------- Existing submissions from the old forms ---------------- */

    public function test_old_submissions_are_copied_into_reports_without_touching_the_old_tables(): void
    {
        $dept = $this->department('Cell Ministry');

        $deptRow = DB::table('department_citations')->insertGetId([
            'title' => 'Bro', 'fullname' => 'PAUL ORIFE', 'unit' => 'Operation', 'designation' => 'Developer',
            'kingschat' => 'orife001', 'phone' => '08136386103', 'department' => 'Cell Ministry',
            'period' => 'January 1, 2026 to November 30, 2026',
            'citation' => 'Personal citation text', 'unit_citation' => 'Unit citation text',
            'personal_report_summary' => 'P summary', 'personal_report_detail' => 'P detail',
            'unit_report_summary' => null, 'unit_report_detail' => null,
            'approved' => true, 'admin_comment' => 'Good',
            'created_at' => '2026-05-23 11:38:41', 'updated_at' => '2026-05-23 11:38:41',
        ]);

        DB::table('group_citations')->insert([
            'title' => 'Sis', 'name' => 'Jane Doe', 'unit' => 'Choir', 'designation' => 'Member',
            'kingschat' => '@jane', 'phone' => '0803', 'group_name' => 'Youth Group',
            'period_from' => '2026-02-01', 'period_to' => '2026-09-30',
            'description' => 'Group personal citation',
            'created_at' => '2026-06-01 09:00:00', 'updated_at' => '2026-06-01 09:00:00',
        ]);

        // A file uploaded with the old department submission
        $legacyFileId = DB::table('citation_attachments')->insertGetId([
            'attachable_type' => 'App\\Models\\DepartmentCitation', 'attachable_id' => $deptRow,
            'kind' => 'document', 'original_name' => 'old.pdf', 'path' => 'citation-attachments/old.pdf',
            'disk' => 'local', 'mime_type' => 'application/pdf', 'size' => 10,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_09_100100_copy_legacy_submissions_into_reports.php');
        $migration->up();

        // Department row -> 3 reports (personal citation, unit citation, personal report); no unit report
        $this->assertSame(
            ['personal_citation', 'personal_report', 'unit_citation'],
            Report::where('name', 'PAUL ORIFE')->orderBy('type')->pluck('type')->all()
        );

        $citation = Report::where('name', 'PAUL ORIFE')->where('type', 'personal_citation')->firstOrFail();
        $this->assertSame('Personal citation text', $citation->citation);
        $this->assertSame('Cell Ministry', $citation->entity_name);
        $this->assertSame('department', $citation->entity_type);
        $this->assertSame($dept->id, $citation->entity_id);
        $this->assertSame('2026-01-01', $citation->period_from->toDateString());
        $this->assertSame('2026-11-30', $citation->period_to->toDateString());
        $this->assertTrue($citation->approved);
        $this->assertSame('Good', $citation->admin_comment);
        $this->assertSame('2026-05-23 11:38:41', $citation->created_at->format('Y-m-d H:i:s'));

        $report = Report::where('name', 'PAUL ORIFE')->where('type', 'personal_report')->firstOrFail();
        $this->assertSame('P summary', $report->executive_summary);
        $this->assertSame('P detail', $report->detailed_report);

        // Group row -> one personal citation, recorded as a group
        $group = Report::where('name', 'Jane Doe')->firstOrFail();
        $this->assertSame('personal_citation', $group->type);
        $this->assertSame('group', $group->entity_type);
        $this->assertSame('Youth Group', $group->entity_name);
        $this->assertSame('2026-02-01', $group->period_from->toDateString());

        // The uploaded file now belongs to the first report made from its row
        $legacyFile = CitationAttachment::findOrFail($legacyFileId);
        $this->assertSame(Report::class, $legacyFile->attachable_type);
        $this->assertSame($citation->id, $legacyFile->attachable_id);

        // Old tables untouched
        $this->assertSame(1, DB::table('department_citations')->count());
        $this->assertSame(1, DB::table('group_citations')->count());

        // Running it again does nothing
        $migration->up();
        $this->assertSame(4, Report::count());
    }
}
