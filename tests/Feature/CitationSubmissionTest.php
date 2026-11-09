<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CitationAttachment;
use App\Models\DepartmentCitation;
use App\Models\GroupCitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function words(int $n): string
    {
        return implode(' ', array_fill(0, $n, 'word'));
    }

    private function departmentPayload(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'Bro',
            'fullname'    => 'John Doe',
            'unit'        => 'Ushers',
            'designation' => 'Leader',
            'kingschat'   => '@john',
            'phone'       => '+2348000000000',
            'department'  => 'Finance',
            'period'      => 'January 1, 2026 to October 1, 2026',
            'citation'                => 'My personal citation.',
            'unit_citation'           => 'The unit citation.',
            'personal_report_summary' => 'Personal summary.',
            'personal_report_detail'  => 'Personal detail.',
            'unit_report_summary'     => 'Unit summary.',
            'unit_report_detail'      => 'Unit detail.',
        ], $overrides);
    }

    private function groupPayload(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'Sis',
            'name'        => 'Jane Doe',
            'unit'        => 'Choir',
            'designation' => 'Member',
            'kingschat'   => '@jane',
            'phone'       => '+2348000000001',
            'group_name'  => 'Youth Group',
            'period'      => 'January 1, 2026 to October 1, 2026',
            'description'             => 'My personal citation.',
            'unit_citation'           => 'The group citation.',
            'personal_report_summary' => 'Personal summary.',
            'personal_report_detail'  => 'Personal detail.',
            'unit_report_summary'     => 'Unit summary.',
            'unit_report_detail'      => 'Unit detail.',
        ], $overrides);
    }

    /* ---------------- Forms render ---------------- */

    public function test_forms_show_every_new_field_and_the_upload_input(): void
    {
        foreach (['/department-form', '/group-form'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('enctype="multipart/form-data"', false)
                ->assertSee('name="attachments[]"', false)
                ->assertSee('name="personal_report_summary"', false)
                ->assertSee('name="personal_report_detail"', false)
                ->assertSee('name="unit_report_summary"', false)
                ->assertSee('name="unit_report_detail"', false)
                ->assertSee('name="unit_citation"', false)
                ->assertSee('Executive Summary (1 page)')
                ->assertSee('(max 150 words)');
        }
    }

    /* ---------------- Department submission ---------------- */

    public function test_department_submission_saves_reports_and_both_citations(): void
    {
        $this->post('/department-form', $this->departmentPayload())
            ->assertRedirect(route('department-form.create'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('department_citations', [
            'fullname'                => 'John Doe',
            'citation'                => 'My personal citation.',
            'unit_citation'           => 'The unit citation.',
            'personal_report_summary' => 'Personal summary.',
            'personal_report_detail'  => 'Personal detail.',
            'unit_report_summary'     => 'Unit summary.',
            'unit_report_detail'      => 'Unit detail.',
        ]);
    }

    public function test_citations_over_150_words_are_rejected(): void
    {
        $this->post('/department-form', $this->departmentPayload(['citation' => $this->words(151)]))
            ->assertSessionHasErrors('citation');

        $this->post('/department-form', $this->departmentPayload(['unit_citation' => $this->words(151)]))
            ->assertSessionHasErrors('unit_citation');

        $this->post('/group-form', $this->groupPayload(['description' => $this->words(151)]))
            ->assertSessionHasErrors('description');

        $this->post('/group-form', $this->groupPayload(['unit_citation' => $this->words(151)]))
            ->assertSessionHasErrors('unit_citation');

        $this->assertDatabaseCount('department_citations', 0);
        $this->assertDatabaseCount('group_citations', 0);
    }

    public function test_exactly_150_words_is_accepted(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'citation'      => $this->words(150),
            'unit_citation' => $this->words(150),
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('department_citations', 1);
    }

    public function test_numbers_and_accented_words_count_as_words(): void
    {
        // 151 tokens made of digits and accented letters: str_word_count() would miss these
        $text = implode(' ', array_fill(0, 151, '2026ẹ̀'));

        $this->post('/department-form', $this->departmentPayload(['citation' => $text]))
            ->assertSessionHasErrors('citation');
    }

    public function test_report_summary_and_detail_word_limits_are_enforced(): void
    {
        $summaryMax = config('citation.max_words.report_summary');
        $detailMax  = config('citation.max_words.report_detail');

        $this->post('/department-form', $this->departmentPayload(['personal_report_summary' => $this->words($summaryMax + 1)]))
            ->assertSessionHasErrors('personal_report_summary');

        $this->post('/department-form', $this->departmentPayload(['unit_report_summary' => $this->words($summaryMax + 1)]))
            ->assertSessionHasErrors('unit_report_summary');

        $this->post('/department-form', $this->departmentPayload(['personal_report_detail' => $this->words($detailMax + 1)]))
            ->assertSessionHasErrors('personal_report_detail');

        $this->post('/department-form', $this->departmentPayload(['unit_report_detail' => $this->words($detailMax + 1)]))
            ->assertSessionHasErrors('unit_report_detail');
    }

    public function test_every_new_text_field_is_required(): void
    {
        foreach ([
            'unit_citation', 'personal_report_summary', 'personal_report_detail',
            'unit_report_summary', 'unit_report_detail',
        ] as $field) {
            $this->post('/department-form', $this->departmentPayload([$field => '']))
                ->assertSessionHasErrors($field);

            $this->post('/group-form', $this->groupPayload([$field => '']))
                ->assertSessionHasErrors($field);
        }
    }

    public function test_visitors_cannot_set_admin_only_fields(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'approved'      => 1,
            'admin_comment' => 'Pre-approved by me',
        ]))->assertSessionHasNoErrors();

        $citation = DepartmentCitation::firstOrFail();
        $this->assertFalse($citation->approved);
        $this->assertNull($citation->admin_comment);
    }

    /* ---------------- Group submission ---------------- */

    public function test_group_submission_saves_reports_and_both_citations(): void
    {
        $this->post('/group-form', $this->groupPayload())
            ->assertRedirect(route('group-citations.create'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('group_citations', [
            'name'                    => 'Jane Doe',
            'description'             => 'My personal citation.',
            'unit_citation'           => 'The group citation.',
            'personal_report_summary' => 'Personal summary.',
            'personal_report_detail'  => 'Personal detail.',
            'unit_report_summary'     => 'Unit summary.',
            'unit_report_detail'      => 'Unit detail.',
        ]);
    }

    /* ---------------- Uploads ---------------- */

    public function test_uploads_are_optional(): void
    {
        $this->post('/department-form', $this->departmentPayload())->assertSessionHasNoErrors();

        $this->assertDatabaseCount('citation_attachments', 0);
    }

    public function test_document_video_and_audio_files_are_stored_privately(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'attachments' => [
                UploadedFile::fake()->create('report.pdf', 200, 'application/pdf'),
                UploadedFile::fake()->create('highlights.mp4', 2000, 'video/mp4'),
                UploadedFile::fake()->create('testimony.mp3', 500, 'audio/mpeg'),
            ],
        ]))->assertSessionHasNoErrors();

        $citation = DepartmentCitation::with('attachments')->firstOrFail();
        $this->assertCount(3, $citation->attachments);

        $this->assertEqualsCanonicalizing(
            ['document', 'video', 'audio'],
            $citation->attachments->pluck('kind')->all()
        );

        foreach ($citation->attachments as $file) {
            Storage::disk('local')->assertExists($file->path);
            // The visitor's file name is never used on disk
            $this->assertStringNotContainsString('report', $file->path);
            $this->assertStringNotContainsString('highlights', $file->path);
        }
    }

    public function test_group_submission_accepts_uploads_too(): void
    {
        $this->post('/group-form', $this->groupPayload([
            'attachments' => [UploadedFile::fake()->create('slides.pptx', 300, 'application/vnd.openxmlformats-officedocument.presentationml.presentation')],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, GroupCitation::firstOrFail()->attachments()->count());
    }

    public function test_dangerous_file_types_are_rejected(): void
    {
        foreach (['shell.php', 'run.exe', 'page.html', 'x.js', 'image.svg'] as $name) {
            $this->post('/department-form', $this->departmentPayload([
                'attachments' => [UploadedFile::fake()->create($name, 10)],
            ]))->assertSessionHasErrors('attachments.0');
        }

        $this->assertDatabaseCount('department_citations', 0);
        $this->assertDatabaseCount('citation_attachments', 0);
    }

    public function test_a_php_file_renamed_to_pdf_is_rejected_by_content_check(): void
    {
        // A real file on disk (not UploadedFile::fake(), which guesses the type from the
        // name), so the server sniffs the actual content like it does for real uploads.
        $tmp = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($tmp, '<?php echo "owned"; ?>');
        $fake = new UploadedFile($tmp, 'invoice.pdf', 'application/pdf', null, true);

        $this->post('/department-form', $this->departmentPayload(['attachments' => [$fake]]))
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('department_citations', 0);
    }

    public function test_file_count_and_size_limits_are_enforced(): void
    {
        $max = config('citation.uploads.max_files');

        $tooMany = array_map(
            fn ($i) => UploadedFile::fake()->create("doc{$i}.pdf", 10, 'application/pdf'),
            range(1, $max + 1)
        );
        $this->post('/department-form', $this->departmentPayload(['attachments' => $tooMany]))
            ->assertSessionHasErrors('attachments');

        $tooBig = UploadedFile::fake()->create('big.pdf', config('citation.uploads.max_size_kb') + 1, 'application/pdf');
        $this->post('/department-form', $this->departmentPayload(['attachments' => [$tooBig]]))
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('department_citations', 0);
    }

    public function test_a_rejected_submission_leaves_no_files_behind(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'citation'    => $this->words(151),
            'attachments' => [UploadedFile::fake()->create('report.pdf', 100, 'application/pdf')],
        ]))->assertSessionHasErrors('citation');

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    /* ---------------- Admin side ---------------- */

    public function test_only_admins_can_download_attachments(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'attachments' => [UploadedFile::fake()->create('secret.pdf', 50, 'application/pdf')],
        ]));
        $attachment = CitationAttachment::firstOrFail();
        $url = route('admin.attachments.download', $attachment);

        // Guest: sent to the login page, no file
        $this->get($url)->assertRedirect();

        // Admin: gets the file under its original name
        $admin = Admin::create(['name' => 'Admin', 'email' => 'a@example.com', 'password' => bcrypt('secret-pass')]);
        $this->actingAs($admin, 'admin')
            ->get($url)
            ->assertOk()
            ->assertDownload('secret.pdf');
    }

    public function test_attachment_files_are_not_publicly_reachable(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'attachments' => [UploadedFile::fake()->create('secret.pdf', 50, 'application/pdf')],
        ]));
        $attachment = CitationAttachment::firstOrFail();

        // The stored path is on the private "local" disk, not the public disk
        $this->assertSame('local', $attachment->disk);
        Storage::disk('public')->assertMissing($attachment->path);
    }

    public function test_deleting_an_attachment_record_removes_its_file(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'attachments' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
        ]));
        $attachment = CitationAttachment::firstOrFail();
        $path = $attachment->path;

        $attachment->delete();

        Storage::disk('local')->assertMissing($path);
    }

    public function test_dashboard_shows_the_new_submission_details_and_download_link(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'unit_report_detail' => 'Detailed unit story',
            'attachments'        => [UploadedFile::fake()->create('proof.pdf', 50, 'application/pdf')],
        ]));
        $this->post('/group-form', $this->groupPayload());

        $admin = Admin::create(['name' => 'Admin', 'email' => 'a@example.com', 'password' => bcrypt('secret-pass')]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('View full submission')
            ->assertSee('Detailed unit story')
            ->assertSee('proof.pdf')
            // The URL sits inside Js::from() JSON, where "/" is escaped as three backslashes + "/"
            ->assertSee('admin' . str_repeat('\\', 3) . '/attachments' . str_repeat('\\', 3) . '/' . CitationAttachment::firstOrFail()->id, false);
    }

    public function test_exports_include_the_new_columns(): void
    {
        $this->post('/department-form', $this->departmentPayload([
            'attachments' => [UploadedFile::fake()->create('proof.pdf', 50, 'application/pdf')],
        ]));
        $this->post('/group-form', $this->groupPayload());

        $admin = Admin::create(['name' => 'Admin', 'email' => 'a@example.com', 'password' => bcrypt('secret-pass')]);
        $this->actingAs($admin, 'admin');

        $csv = $this->get('/departments/export/csv')->assertOk()->streamedContent();
        $this->assertStringContainsString('Unit / Department Citation', $csv);
        $this->assertStringContainsString('The unit citation.', $csv);
        $this->assertStringContainsString('Unit detail.', $csv);
        $this->assertStringContainsString('proof.pdf', $csv);

        $this->get('/departments/export/excel')->assertOk();
        $this->get('/groups/export/excel')->assertOk();
        $this->get('/groups/export/csv')->assertOk();
    }
}
