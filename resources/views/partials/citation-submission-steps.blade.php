{{--
    Steps 3 to 5 of the submission forms (shared by the department and group forms):

      Step 3  Personal submission   : personal annual report + personal citation
      Step 4  Unit / Department     : unit annual report + unit citation
      Step 5  Uploads               : document, video or audio files, then Submit

    Expected variables:
      $unitLabel              e.g. "Unit / Department"
      $personalCitationField  form field name of the personal citation
                              ("citation" on the department form, "description" on the group form)
--}}
@php
    $words   = config('citation.max_words');
    $uploads = config('citation.uploads');

    $allExtensions = collect($uploads['types'])->flatten()->all();
    $maxMb         = (int) round($uploads['max_size_kb'] / 1024);

    // True when validation failed on one of these fields (so the form can reopen that step)
    $stepHasError = fn (array $prefixes) => collect($errors->keys())
        ->contains(fn ($key) => \Illuminate\Support\Str::startsWith($key, $prefixes));
@endphp

<!-- Step 3: Personal submission -->
<div class="form-step hidden space-y-8"
     data-has-error="{{ $stepHasError(['period', 'personal_report_', $personalCitationField]) ? 1 : 0 }}">
    <h2 class="text-xl font-semibold flex items-center space-x-2">
        <i data-feather="user-check" class="w-6 h-6 text-white"></i>
        <span>Step 3: Your Personal Submission</span>
    </h2>

    <x-form.datepicker name="period" label="Period" placeholder="Select period (From – To)" />

    <section class="space-y-4">
        <h3 class="text-lg font-semibold border-b border-white/20 pb-2">Personal Annual Report</h3>
        <p class="text-sm text-white/80">
            Include the ideas and innovations you executed, and your significant contributions to the zone and the ministry.
        </p>

        <x-form.word-textarea name="personal_report_summary" label="Executive Summary (1 page)"
            :max="$words['report_summary']" rows="9"
            placeholder="A one-page summary of your year." />

        <x-form.word-textarea name="personal_report_detail" label="Detailed Report"
            :max="$words['report_detail']" rows="14"
            placeholder="The full detail of your year: what you did, the results, and your contributions." />
    </section>

    <section class="space-y-4">
        <h3 class="text-lg font-semibold border-b border-white/20 pb-2">Personal Citation</h3>

        <x-form.word-textarea :name="$personalCitationField" label="Personal Citation"
            :max="$words['citation']" rows="6"
            placeholder="Your citation (max {{ $words['citation'] }} words)" />
    </section>

    <div class="flex justify-between mt-4">
        <button type="button"
            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
            onclick="prevStep()">Previous</button>
        <button type="button"
            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
            onclick="nextStep()">Next</button>
    </div>
</div>

<!-- Step 4: Unit / Department submission -->
<div class="form-step hidden space-y-8"
     data-has-error="{{ $stepHasError(['unit_report_', 'unit_citation']) ? 1 : 0 }}">
    <h2 class="text-xl font-semibold flex items-center space-x-2">
        <i data-feather="users" class="w-6 h-6 text-white"></i>
        <span>Step 4: {{ $unitLabel }} Submission</span>
    </h2>

    <section class="space-y-4">
        <h3 class="text-lg font-semibold border-b border-white/20 pb-2">Annual Report: your Unit, Sub-department or Department</h3>

        <x-form.word-textarea name="unit_report_summary" label="Executive Summary (1 page)"
            :max="$words['report_summary']" rows="9"
            placeholder="A one-page summary of the year for your unit, sub-department or department." />

        <x-form.word-textarea name="unit_report_detail" label="Detailed Report"
            :max="$words['report_detail']" rows="14"
            placeholder="The full detail of the year for your unit, sub-department or department." />
    </section>

    <section class="space-y-4">
        <h3 class="text-lg font-semibold border-b border-white/20 pb-2">{{ $unitLabel }} Citation</h3>

        <x-form.word-textarea name="unit_citation" label="{{ $unitLabel }} Citation"
            :max="$words['citation']" rows="6"
            placeholder="The citation for your unit or department (max {{ $words['citation'] }} words)" />
    </section>

    <div class="flex justify-between mt-4">
        <button type="button"
            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
            onclick="prevStep()">Previous</button>
        <button type="button"
            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
            onclick="nextStep()">Next</button>
    </div>
</div>

<!-- Step 5: Uploads + Submit -->
<div class="form-step hidden space-y-6"
     data-has-error="{{ $stepHasError(['attachments']) ? 1 : 0 }}">
    <h2 class="text-xl font-semibold flex items-center space-x-2">
        <i data-feather="upload-cloud" class="w-6 h-6 text-white"></i>
        <span>Step 5: Upload Files <span class="font-normal text-white/70">(optional)</span></span>
    </h2>

    <div class="rounded-2xl border-2 border-dashed border-white/40 bg-white/5 p-6">
        <label for="attachments" class="block text-center font-semibold">
            Attach a document, video or audio file
        </label>
        <p class="mt-1 text-center text-sm text-white/80">
            Documents (PDF, Word, PowerPoint), video (MP4, MOV, ...) or audio (MP3, WAV, M4A, ...).<br>
            Up to {{ $uploads['max_files'] }} files, {{ $maxMb }} MB each.
        </p>

        <input
            id="attachments"
            type="file"
            name="attachments[]"
            multiple
            accept=".{{ implode(',.', $allExtensions) }}"
            data-max-files="{{ $uploads['max_files'] }}"
            data-max-bytes="{{ $uploads['max_size_kb'] * 1024 }}"
            data-extensions="{{ implode(',', $allExtensions) }}"
            class="mt-4 block w-full text-sm text-white file:mr-4 file:rounded-lg file:border-0 file:bg-white file:px-4 file:py-2 file:font-semibold file:text-indigo-600 hover:file:bg-indigo-50">

        <ul id="attachmentList" class="mt-4 space-y-1 text-sm"></ul>
        <p id="attachmentError" class="mt-3 hidden text-sm text-red-200" role="alert"></p>

        @foreach ($errors->get('attachments*') as $messages)
            @foreach ((array) $messages as $message)
                <p class="mt-3 text-sm text-red-200">{{ $message }}</p>
            @endforeach
        @endforeach

        @if ($errors->any())
            <p class="mt-3 text-sm text-white/80">If you had chosen files before, please attach them again. Browsers cannot keep them after a page reload.</p>
        @endif
    </div>

    <div class="flex justify-between mt-4">
        <button type="button"
            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
            onclick="prevStep()">Previous</button>
        <button id="submitBtn" type="submit"
            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50 disabled:opacity-60 disabled:cursor-not-allowed">Submit</button>
    </div>
</div>
