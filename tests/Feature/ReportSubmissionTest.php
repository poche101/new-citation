<?php

namespace Tests\Feature;

use App\Models\CitationAttachment;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MakesReports;
use Tests\TestCase;

class ReportSubmissionTest extends TestCase
{
    use MakesReports, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /* ---------------- Landing and navigation ---------------- */

    public function test_landing_page_has_a_submit_a_report_button_to_the_chooser(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Submit a report')
            ->assertSee(route('reports.choose'), false);
    }

    public function test_chooser_lists_all_four_reports_with_their_requirements(): void
    {
        $response = $this->get('/submit')->assertOk();

        foreach (config('citation.reports') as $type) {
            $response->assertSee($type['label'])
                ->assertSee(route('reports.entities', $type['slug']), false);
        }

        $response->assertSee('Upload your report as a document or sheet')
            ->assertSee('Not more than 150 words')
            ->assertDontSee('executive summary', false)
            ->assertDontSee('Detailed report');
    }

    public function test_department_and_group_picker_lists_both(): void
    {
        $this->department('Finance');
        $this->department('Youth Group', 'group');

        $this->get('/submit/personal-citation')
            ->assertOk()
            ->assertSee('Finance')
            ->assertSee('Youth Group')
            ->assertSee('Departments')
            ->assertSee('Groups');
    }

    public function test_picker_links_go_to_the_form_for_that_department(): void
    {
        $dept = $this->department('Finance');

        $this->get('/submit/unit-report')
            ->assertSee(route('reports.create', ['unit-report', $dept->id]), false);
    }

    public function test_picker_explains_when_nothing_has_been_added(): void
    {
        $this->get('/submit/personal-report')->assertOk()->assertSee('No departments or groups have been added yet');
    }

    public function test_unknown_report_type_and_unknown_department_are_404(): void
    {
        $dept = $this->department();

        $this->get('/submit/not-a-report')->assertNotFound();
        $this->get('/submit/personal-report/9999')->assertNotFound();
        $this->get("/submit/not-a-report/{$dept->id}")->assertNotFound();
    }

    public function test_old_links_redirect_to_the_new_flow(): void
    {
        foreach (['/department-form', '/group-form', '/group-form/3', '/departments', '/groups'] as $old) {
            $this->get($old)->assertRedirect('/submit')->assertStatus(301);
        }
    }

    /* ---------------- The forms ---------------- */

    public function test_report_forms_have_dates_and_a_required_upload_and_no_text_boxes(): void
    {
        $dept = $this->department();

        foreach (['personal-report', 'unit-report'] as $slug) {
            $this->get("/submit/{$slug}/{$dept->id}")
                ->assertOk()
                ->assertSee('enctype="multipart/form-data"', false)
                ->assertDontSee('name="executive_summary"', false)
                ->assertDontSee('name="detailed_report"', false)
                ->assertDontSee('name="citation"', false)
                ->assertSee('name="period_from"', false)
                ->assertSee('name="period_to"', false)
                ->assertSee('name="attachments[]"', false)
                ->assertSee('Upload your report')
                ->assertSee('Finance');
        }
    }

    public function test_citation_forms_have_a_150_word_citation_dates_and_upload(): void
    {
        $dept = $this->department();

        foreach (['personal-citation', 'unit-citation'] as $slug) {
            $this->get("/submit/{$slug}/{$dept->id}")
                ->assertOk()
                ->assertSee('name="citation"', false)
                ->assertSee('(up to 150 words)')
                ->assertDontSee('name="executive_summary"', false)
                ->assertDontSee('name="detailed_report"', false)
                ->assertSee('name="attachments[]"', false);
        }
    }

    public function test_dates_are_clearly_labelled_and_prefilled_with_the_current_year(): void
    {
        $dept = $this->department();

        $this->get("/submit/personal-report/{$dept->id}")
            ->assertSee('From (first day)')
            ->assertSee('To (last day)')
            ->assertSee('value="' . now()->startOfYear()->toDateString() . '"', false)
            ->assertSee('value="' . now()->endOfYear()->toDateString() . '"', false)
            ->assertSee('type="date"', false);
    }

    /* ---------------- Submitting each type ---------------- */

    public function test_each_of_the_four_types_saves_with_the_department_recorded(): void
    {
        $dept = $this->department('Finance');

        foreach (array_keys(config('citation.reports')) as $type) {
            $this->post($this->submitUrl($type, $dept), $this->payload($type))
                ->assertRedirect(route('reports.done'))
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(4, Report::count());

        foreach (config('citation.reports') as $type => $config) {
            $report = Report::where('type', $type)->firstOrFail();

            $this->assertSame('Finance', $report->entity_name);
            $this->assertSame('department', $report->entity_type);
            $this->assertSame($dept->id, $report->entity_id);
            $this->assertSame('2026-01-01', $report->period_from->toDateString());
            $this->assertSame('2026-11-30', $report->period_to->toDateString());

            if ($config['kind'] === 'report') {
                $this->assertNull($report->executive_summary);
                $this->assertNull($report->detailed_report);
                $this->assertNull($report->citation);
                $this->assertSame(1, $report->attachments()->count());
            } else {
                $this->assertSame('A short citation.', $report->citation);
                $this->assertNull($report->executive_summary);
            }
        }
    }

    public function test_a_group_is_recorded_as_a_group(): void
    {
        $group = $this->department('Youth Group', 'group');

        $this->post($this->submitUrl('personal_citation', $group), $this->payload('personal_citation'))
            ->assertSessionHasNoErrors();

        $report = Report::firstOrFail();
        $this->assertSame('group', $report->entity_type);
        $this->assertSame('Youth Group', $report->entity_name);
    }

    public function test_thank_you_page_shows_what_was_submitted_and_links_onward(): void
    {
        $dept = $this->department('Finance');

        $this->followingRedirects()
            ->post($this->submitUrl('unit_report', $dept), $this->payload('unit_report'))
            ->assertOk()
            ->assertSee('Your submission has been received')
            ->assertSee('Finance')
            ->assertSee('Submit another report');
    }

    public function test_visiting_the_thank_you_page_directly_goes_back_to_the_chooser(): void
    {
        $this->get('/submit/done')->assertRedirect(route('reports.choose'));
    }

    public function test_renaming_a_department_later_does_not_change_old_submissions(): void
    {
        $dept = $this->department('Finance');
        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation'));

        $dept->update(['name' => 'Finance and Accounts']);

        $this->assertSame('Finance', Report::firstOrFail()->entity_name);
    }

    /* ---------------- Limits and validation ---------------- */

    public function test_citations_over_150_words_are_rejected_and_exactly_150_is_accepted(): void
    {
        $dept = $this->department();

        foreach (['personal_citation', 'unit_citation'] as $type) {
            $this->post($this->submitUrl($type, $dept), $this->payload($type, ['citation' => $this->words(151)]))
                ->assertSessionHasErrors('citation');

            $this->post($this->submitUrl($type, $dept), $this->payload($type, ['citation' => $this->words(150)]))
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(2, Report::count());
    }

    public function test_numbers_and_accented_words_count_as_words(): void
    {
        $dept = $this->department();

        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', [
            'citation' => implode(' ', array_fill(0, 151, '2026ẹ̀')),
        ]))->assertSessionHasErrors('citation');
    }

    public function test_annual_reports_need_a_file_and_ignore_typed_text(): void
    {
        $dept = $this->department();

        foreach (['personal_report', 'unit_report'] as $type) {
            $this->post($this->submitUrl($type, $dept), $this->payload($type, ['attachments' => []]))
                ->assertSessionHasErrors('attachments');

            // Text sent by hand is not saved: only validated fields are stored
            $this->post($this->submitUrl($type, $dept), $this->payload($type, ['executive_summary' => 'x', 'detailed_report' => 'y']))
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(2, Report::count());
        $this->assertNull(Report::first()->executive_summary);
    }

    public function test_required_fields_are_enforced(): void
    {
        $dept = $this->department();

        foreach (['name', 'unit', 'designation', 'kingschat', 'phone', 'period_from', 'period_to'] as $field) {
            $this->post($this->submitUrl('personal_report', $dept), $this->payload('personal_report', [$field => '']))
                ->assertSessionHasErrors($field);
        }

        $this->post($this->submitUrl('unit_citation', $dept), $this->payload('unit_citation', ['citation' => '']))
            ->assertSessionHasErrors('citation');

        // Title is the only optional field
        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', ['title' => '']))
            ->assertSessionHasNoErrors();
    }

    public function test_the_to_date_cannot_be_before_the_from_date(): void
    {
        $dept = $this->department();

        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', [
            'period_from' => '2026-06-01',
            'period_to'   => '2026-01-01',
        ]))->assertSessionHasErrors(['period_to' => 'The "To" date must be the same as or later than the "From" date.']);

        // A single day is fine
        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', [
            'period_from' => '2026-06-01',
            'period_to'   => '2026-06-01',
        ]))->assertSessionHasNoErrors();
    }

    public function test_visitors_cannot_set_review_fields_type_or_department(): void
    {
        $dept = $this->department('Finance');
        $other = $this->department('Sceptre');

        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', [
            'approved'      => 1,
            'admin_comment' => 'Pre-approved',
            'type'          => 'unit_report',
            'entity_name'   => 'Sceptre',
            'entity_id'     => $other->id,
        ]))->assertSessionHasNoErrors();

        $report = Report::firstOrFail();
        $this->assertFalse($report->approved);
        $this->assertNull($report->admin_comment);
        $this->assertSame('personal_citation', $report->type);
        $this->assertSame('Finance', $report->entity_name);
    }

    public function test_a_failed_submit_keeps_what_was_typed(): void
    {
        $dept = $this->department();

        $this->from("/submit/personal-citation/{$dept->id}")
            ->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', ['citation' => $this->words(151), 'name' => 'Keep Me']))
            ->assertRedirect("/submit/personal-citation/{$dept->id}")
            ->assertSessionHasInput('name', 'Keep Me');
    }

    /* ---------------- Uploads ---------------- */

    public function test_the_form_offers_documents_and_spreadsheets_only(): void
    {
        $dept = $this->department();

        foreach (['personal_report', 'personal_citation'] as $type) {
            $this->get(route('reports.create', [config("citation.reports.{$type}.slug"), $dept->id]))
                ->assertOk()
                ->assertSee('id="upload_files"', false)
                ->assertDontSee('upload_media', false)
                ->assertDontSee('Audio or video')
                ->assertDontSee('.mp3', false)
                ->assertDontSee('.mp4', false);
        }
    }

    public function test_audio_and_video_files_are_refused(): void
    {
        $dept = $this->department();

        foreach ([['talk.mp3', 'audio/mpeg'], ['clip.mp4', 'video/mp4']] as [$name, $mime]) {
            $this->post($this->submitUrl('personal_report', $dept), $this->payload('personal_report', [
                'attachments' => [\Illuminate\Http\UploadedFile::fake()->create($name, 100, $mime)],
            ]))->assertSessionHasErrors('attachments.0');
        }

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_a_post_that_is_too_large_returns_to_the_form_with_a_clear_message(): void
    {
        $dept = $this->department();
        $url  = "/submit/personal-report/{$dept->id}";

        // Simulate PHP rejecting a body bigger than post_max_size
        $this->app['router']->post('/__too-large', fn () => throw new \Illuminate\Http\Exceptions\PostTooLargeException())->middleware('web');

        $this->from($url)->post('/__too-large')->assertRedirect($url . '?upload_error=too_large');

        $this->get($url . '?upload_error=too_large')
            ->assertOk()
            ->assertSee('too big to send in one go');
    }

    public function test_uploads_are_optional_for_citations(): void
    {
        $dept = $this->department();

        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation'))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('citation_attachments', 0);
    }

    public function test_an_annual_report_cannot_be_submitted_without_a_file(): void
    {
        $dept = $this->department();

        foreach (['personal_report', 'unit_report'] as $type) {
            $this->post($this->submitUrl($type, $dept), $this->payload($type, ['attachments' => []]))
                ->assertSessionHasErrors('attachments');
        }

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_document_and_spreadsheet_files_are_stored_privately(): void
    {
        $dept = $this->department();

        $this->post($this->submitUrl('personal_report', $dept), $this->payload('personal_report', [
            'attachments' => [
                $this->pdf('report.pdf'),
                UploadedFile::fake()->create('figures.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ],
        ]))->assertSessionHasNoErrors();

        $report = Report::with('attachments')->firstOrFail();

        $this->assertEqualsCanonicalizing(['document', 'sheet'], $report->attachments->pluck('kind')->all());

        foreach ($report->attachments as $file) {
            Storage::disk('local')->assertExists($file->path);
            $this->assertSame('local', $file->disk);
            Storage::disk('public')->assertMissing($file->path);
            // The visitor's own file name is never used on disk
            $this->assertStringNotContainsString('figures', $file->path);
        }
    }

    public function test_every_report_type_accepts_uploads(): void
    {
        $dept = $this->department();

        foreach (array_keys(config('citation.reports')) as $type) {
            $this->post($this->submitUrl($type, $dept), $this->payload($type, ['attachments' => [$this->pdf()]]))
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(4, CitationAttachment::count());
    }

    public function test_dangerous_file_types_are_rejected(): void
    {
        $dept = $this->department();

        foreach (['shell.php', 'run.exe', 'page.html', 'x.js', 'image.svg'] as $name) {
            $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', [
                'attachments' => [UploadedFile::fake()->create($name, 10)],
            ]))->assertSessionHasErrors('attachments.0');
        }

        $this->assertSame(0, Report::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_a_php_file_renamed_to_pdf_is_rejected_by_the_content_check(): void
    {
        $dept = $this->department();

        // A real file on disk (UploadedFile::fake() guesses the type from the name)
        $tmp = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($tmp, '<?php echo "owned"; ?>');
        $disguised = new UploadedFile($tmp, 'invoice.pdf', 'application/pdf', null, true);

        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', ['attachments' => [$disguised]]))
            ->assertSessionHasErrors('attachments.0');

        $this->assertSame(0, Report::count());
    }

    public function test_file_count_and_size_limits_are_enforced(): void
    {
        $dept = $this->department();
        $max = config('citation.uploads.max_files');

        $tooMany = array_map(fn ($i) => $this->pdf("doc{$i}.pdf", 10), range(1, $max + 1));
        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', ['attachments' => $tooMany]))
            ->assertSessionHasErrors('attachments');

        $tooBig = $this->pdf('big.pdf', config('citation.uploads.max_size_kb') + 1);
        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', ['attachments' => [$tooBig]]))
            ->assertSessionHasErrors('attachments.0');

        $this->assertSame(0, Report::count());
    }

    public function test_a_rejected_submission_leaves_no_files_behind(): void
    {
        $dept = $this->department();

        $this->post($this->submitUrl('personal_citation', $dept), $this->payload('personal_citation', [
            'citation'    => $this->words(151),
            'attachments' => [$this->pdf()],
        ]))->assertSessionHasErrors('citation');

        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
