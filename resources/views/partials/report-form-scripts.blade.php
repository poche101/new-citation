{{--
    Behaviour of the public submission form:
      - step navigation (Continue / Back) with per-step validation
      - live word counters on every [data-word-limit] textarea
      - a plain-language summary of the From / To dates, and a check that "To" is not before "From"
      - checks on the chosen upload files (count, size, type)
      - a final check of ALL steps on submit, so a problem in an earlier step
        takes the person back to it instead of silently blocking the form

    Expects: $submitLabel (text on the final button)
--}}
<script>
(function () {
    const form = document.getElementById('reportForm');
    if (!form) return;

    const SUBMIT_LABEL = @json($submitLabel);
    const steps = Array.from(form.querySelectorAll('.form-step'));
    const bar = document.getElementById('stepProgressBar');
    const label = document.getElementById('stepProgressLabel');
    let current = 0;

    function show(index, scroll = true) {
        current = Math.max(0, Math.min(index, steps.length - 1));
        steps.forEach((step, i) => step.classList.toggle('hidden', i !== current));

        if (bar) bar.style.width = ((current + 1) / steps.length * 100) + '%';
        if (label) label.textContent = 'Step ' + (current + 1) + ' of ' + steps.length + ': ' + (steps[current].dataset.stepName || '');
        if (scroll) window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    const firstInvalid = (step) =>
        Array.from(step.querySelectorAll('input, textarea, select')).find((el) => !el.checkValidity()) || null;

    window.nextStep = function () {
        const bad = firstInvalid(steps[current]);
        if (bad) { bad.reportValidity(); return; }
        show(current + 1);
    };
    window.prevStep = function () { show(current - 1); };

    /* ---------- Live word counters ---------- */
    const countWords = (text) => (text.trim().match(/\S+/g) || []).length;

    form.querySelectorAll('[data-word-limit]').forEach((box) => {
        const max = parseInt(box.dataset.wordLimit, 10);
        const out = form.querySelector('[data-word-counter="' + box.id + '"]');
        if (!out) return;

        const update = () => {
            const n = countWords(box.value);
            const over = n > max;

            out.textContent = over
                ? n.toLocaleString() + ' / ' + max.toLocaleString() + ' words (' + (n - max).toLocaleString() + ' over the limit)'
                : n.toLocaleString() + ' / ' + max.toLocaleString() + ' words';
            out.classList.toggle('text-red-700', over);
            out.classList.toggle('font-semibold', over);
            out.classList.toggle('text-slate-500', !over);

            // Makes Continue / Submit stop on this field until it is short enough
            box.setCustomValidity(over ? 'Please shorten this to ' + max.toLocaleString() + ' words or fewer.' : '');
        };

        box.addEventListener('input', update);
        update();
    });

    /* ---------- From / To dates ---------- */
    const from = document.getElementById('period_from');
    const to = document.getElementById('period_to');
    const summary = document.getElementById('periodSummary');

    function readable(iso) {
        const [y, m, d] = iso.split('-').map(Number);          // avoids timezone shifts
        return new Date(y, m - 1, d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    function updatePeriod() {
        if (!from || !to || !summary) return;

        const bad = from.value && to.value && to.value < from.value;
        to.setCustomValidity(bad ? 'The "To" date must be the same as or later than the "From" date.' : '');

        summary.classList.toggle('bg-royal-50', !bad);
        summary.classList.toggle('text-royal-800', !bad);
        summary.classList.toggle('bg-red-50', bad);
        summary.classList.toggle('text-red-800', bad);

        if (bad) {
            summary.textContent = 'The "To" date is earlier than the "From" date. Please change one of them.';
        } else if (from.value && to.value) {
            summary.textContent = 'Covers ' + readable(from.value) + ' to ' + readable(to.value) + '.';
        } else {
            summary.textContent = 'Choose both a "From" and a "To" date.';
        }
    }

    [from, to].forEach((el) => el && el.addEventListener('input', updatePeriod));
    updatePeriod();

    /* ---------- Upload checks ---------- */
    const fileInput = document.getElementById('attachments');
    const fileList = document.getElementById('attachmentList');
    const fileError = document.getElementById('attachmentError');

    const formatSize = (bytes) => bytes >= 1048576
        ? (bytes / 1048576).toFixed(1) + ' MB'
        : Math.max(1, Math.round(bytes / 1024)) + ' KB';

    if (fileInput && fileList && fileError) {
        const maxFiles = parseInt(fileInput.dataset.maxFiles, 10);
        const maxBytes = parseInt(fileInput.dataset.maxBytes, 10);
        const allowed = fileInput.dataset.extensions.split(',');

        const clearFiles = () => { fileInput.value = ''; fileList.replaceChildren(); };

        fileInput.addEventListener('change', () => {
            const files = Array.from(fileInput.files);
            const problems = [];

            if (files.length > maxFiles) problems.push('Please choose at most ' + maxFiles + ' files.');
            files.forEach((file) => {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!allowed.includes(ext)) problems.push('"' + file.name + '" is not an allowed file type.');
                if (file.size > maxBytes) problems.push('"' + file.name + '" is larger than ' + formatSize(maxBytes) + '.');
            });

            fileError.hidden = problems.length === 0;
            fileError.textContent = problems.join(' ');

            if (problems.length) { clearFiles(); return; }

            // textContent (not innerHTML): file names are untrusted text
            fileList.replaceChildren(...files.map((file) => {
                const li = document.createElement('li');
                li.className = 'flex justify-between gap-4 rounded-lg bg-white px-3 py-2 ring-1 ring-royal-100';

                const name = document.createElement('span');
                name.className = 'truncate font-medium text-royal-900';
                name.textContent = file.name;

                const size = document.createElement('span');
                size.className = 'shrink-0 text-slate-500';
                size.textContent = formatSize(file.size);

                li.append(name, size);
                return li;
            }));

            if (files.length) {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'text-sm font-medium text-royal-700 underline underline-offset-4 hover:text-royal-900';
                remove.textContent = 'Remove all files';
                remove.addEventListener('click', clearFiles);

                const li = document.createElement('li');
                li.append(remove);
                fileList.append(li);
            }
        });
    }

    /* ---------- Submit: check every step, then show progress ---------- */
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', (event) => {
        for (let i = 0; i < steps.length; i++) {
            const bad = firstInvalid(steps[i]);
            if (bad) {
                event.preventDefault();
                show(i);
                bad.reportValidity();
                return;
            }
        }

        if (submitBtn) {
            const hasFiles = fileInput && fileInput.files.length > 0;
            submitBtn.disabled = true;
            submitBtn.textContent = hasFiles ? 'Uploading. Please keep this page open.' : 'Submitting...';
        }
    });

    // Coming back with the browser's Back button must not leave Submit disabled
    window.addEventListener('pageshow', () => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = SUBMIT_LABEL; }
    });

    /* ---------- Start on the right step ---------- */
    // After a failed submit, reopen the first step with an error (data-error-step is 1 to 3, or 0 for none)
    const errorStep = parseInt(form.dataset.errorStep, 10) || 0;
    show(errorStep > 0 ? errorStep - 1 : 0, false);
})();
</script>
