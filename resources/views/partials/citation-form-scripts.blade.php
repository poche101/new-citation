{{--
    Behaviour shared by the department and group forms:
      - step navigation (Next / Previous) with per-step validation
      - live word counters on every [data-word-limit] textarea
      - checks on the chosen upload files (count, size, type)
      - one final check of ALL steps on submit, so a problem in an earlier step
        takes the person back to it instead of silently blocking the form
--}}
<script>
(function () {
    const form = document.getElementById('multiStepForm');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('.form-step'));
    let current = 0;

    const progressBar = document.getElementById('stepProgressBar');
    const progressLabel = document.getElementById('stepProgressLabel');

    function show(index, scroll = true) {
        current = Math.max(0, Math.min(index, steps.length - 1));
        steps.forEach((step, i) => step.classList.toggle('hidden', i !== current));

        if (progressBar) progressBar.style.width = ((current + 1) / steps.length * 100) + '%';
        if (progressLabel) progressLabel.textContent = 'Step ' + (current + 1) + ' of ' + steps.length;
        if (scroll) window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // First field in a step the browser considers invalid (or null if the step is fine)
    function firstInvalid(step) {
        return Array.from(step.querySelectorAll('input, textarea, select'))
            .find((el) => !el.checkValidity()) || null;
    }

    // Read-only "pick from the list" fields are skipped by browser validation,
    // so check them ourselves.
    function emptySelection(step) {
        return Array.from(step.querySelectorAll('input[readonly][required]'))
            .find((el) => !el.value.trim()) || null;
    }

    function blockedBy(step) {
        const empty = emptySelection(step);
        if (empty) {
            alert(empty.dataset.missingMessage || 'Please make a selection before continuing.');
            return true;
        }
        const bad = firstInvalid(step);
        if (bad) {
            bad.reportValidity();
            return true;
        }
        return false;
    }

    window.nextStep = function () {
        if (blockedBy(steps[current])) return;
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
            out.classList.toggle('text-red-300', over);
            out.classList.toggle('font-semibold', over);
            out.classList.toggle('text-white/70', !over);

            // Makes Next / Submit stop on this field until it is short enough
            box.setCustomValidity(over ? 'Please shorten this to ' + max.toLocaleString() + ' words or fewer.' : '');
        };

        box.addEventListener('input', update);
        update();
    });

    /* ---------- Upload checks ---------- */
    const fileInput = document.getElementById('attachments');
    const fileList = document.getElementById('attachmentList');
    const fileError = document.getElementById('attachmentError');

    function formatSize(bytes) {
        return bytes >= 1048576
            ? (bytes / 1048576).toFixed(1) + ' MB'
            : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    if (fileInput && fileList && fileError) {
        const maxFiles = parseInt(fileInput.dataset.maxFiles, 10);
        const maxBytes = parseInt(fileInput.dataset.maxBytes, 10);
        const allowed = fileInput.dataset.extensions.split(',');

        const clearFiles = () => {
            fileInput.value = '';
            fileList.replaceChildren();
        };

        fileInput.addEventListener('change', () => {
            const files = Array.from(fileInput.files);
            const problems = [];

            if (files.length > maxFiles) {
                problems.push('Please choose at most ' + maxFiles + ' files.');
            }
            files.forEach((file) => {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!allowed.includes(ext)) {
                    problems.push('"' + file.name + '" is not an allowed file type.');
                }
                if (file.size > maxBytes) {
                    problems.push('"' + file.name + '" is larger than ' + formatSize(maxBytes) + '.');
                }
            });

            fileError.classList.toggle('hidden', problems.length === 0);
            fileError.textContent = problems.join(' ');

            if (problems.length) {
                clearFiles();
                return;
            }

            // textContent (not innerHTML): file names are untrusted text
            fileList.replaceChildren(...files.map((file) => {
                const li = document.createElement('li');
                li.className = 'flex justify-between gap-4 rounded-lg bg-white/10 px-3 py-2';

                const name = document.createElement('span');
                name.className = 'truncate';
                name.textContent = file.name;

                const size = document.createElement('span');
                size.className = 'shrink-0 text-white/70';
                size.textContent = formatSize(file.size);

                li.append(name, size);
                return li;
            }));

            if (files.length) {
                const clear = document.createElement('button');
                clear.type = 'button';
                clear.className = 'text-sm underline text-white/80 hover:text-white';
                clear.textContent = 'Remove all files';
                clear.addEventListener('click', clearFiles);

                const li = document.createElement('li');
                li.append(clear);
                fileList.append(li);
            }
        });
    }

    /* ---------- Submit: check every step, then show progress ---------- */
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', (event) => {
        for (let i = 0; i < steps.length; i++) {
            const empty = emptySelection(steps[i]);
            const bad = firstInvalid(steps[i]);
            if (empty || bad) {
                event.preventDefault();
                show(i);
                if (empty) {
                    alert(empty.dataset.missingMessage || 'Please make a selection before submitting.');
                } else {
                    bad.reportValidity();
                }
                return;
            }
        }

        if (submitBtn) {
            const hasFiles = fileInput && fileInput.files.length > 0;
            submitBtn.disabled = true;
            submitBtn.textContent = hasFiles ? 'Uploading... please keep this page open' : 'Submitting...';
        }
    });

    // Coming back with the browser's Back button must not leave Submit disabled
    window.addEventListener('pageshow', () => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit';
        }
    });

    /* ---------- Start on the right step ---------- */
    // After a failed submit, reopen the first step that has an error; otherwise start at step 1
    const errorStep = steps.findIndex((step) => step.dataset.hasError === '1');
    show(errorStep >= 0 ? errorStep : 0, false);
})();
</script>
