@extends('layouts.app')

@section('title', 'Group Form - CELZ5 Citation')

@section('content')
<div class="min-h-screen flex flex-col items-center py-10 px-4">

    <!-- Header -->
    <header class="text-center mb-6">
        <h1 class="text-5xl font-bold text-white drop-shadow-lg">CELZ5 Citation</h1>
        <p class="text-white/80 mt-2 text-lg">Submit your Group Citation</p>
    </header>

    <!-- Breadcrumbs -->
    <nav class="text-white text-sm mb-6 w-full max-w-3xl" aria-label="Breadcrumb">
        <ol class="list-reset flex flex-wrap">
            <li>
                <a href="{{ url('/') }}" class="hover:underline flex items-center space-x-1">
                    <i data-feather="home" class="w-4 h-4"></i>
                    <span>Home</span>
                </a>
            </li>
            <li><span class="mx-2">/</span></li>
            <li>
                <a href="{{ url('/groups') }}" class="hover:underline flex items-center space-x-1">
                    <i data-feather="users" class="w-4 h-4"></i>
                    <span>Groups</span>
                </a>
            </li>
            <li><span class="mx-2">/</span></li>
            <li>
                <span id="selectedGroup" class="flex items-center space-x-1">
                    <i data-feather="users" class="w-4 h-4"></i>
                    <span id="selectedGroupName">—</span>
                </span>
            </li>
        </ol>
    </nav>

    <!-- Multi-step Form Wrapper -->
    <div id="multiStepFormWrapper" class="glass p-10 rounded-3xl shadow-2xl w-full max-w-3xl mb-16">

        <!-- Success / Error Messages -->
        @if (session('success'))
            <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    <form id="multiStepForm" class="space-y-6 text-white" method="POST"
        action="{{ route('group-citations.store') }}">
        @csrf

        <!-- Step 1: Personal Info -->
        <div class="form-step space-y-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center space-x-2">
                <i data-feather="user" class="w-6 h-6 text-white"></i>
                <span>Step 1: Personal Information</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="title" label="Title" placeholder="Bro, Sis, Pastor..." icon="user" />
                <x-form.input name="name" label="Full Name" placeholder="John Doe" icon="edit" required />
                <x-form.input name="unit" label="Unit" placeholder="Enter Unit" icon="map-pin" required />
                <x-form.input name="designation" label="Designation" placeholder="e.g. Leader" icon="briefcase" required />
                <x-form.input name="kingschat" label="Kingschat Handle" placeholder="@handle" icon="message-circle" required />
                <x-form.input name="phone" label="Phone Number" placeholder="+234 000 0000" icon="phone" required />
            </div>

            <div class="flex justify-end mt-4">
                <button type="button"
                    class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
                    onclick="nextStep()">Next</button>
            </div>
        </div>

        <!-- Step 2: Group Selection -->
        <div class="form-step hidden space-y-10 w-full">
            <h2 class="text-2xl font-semibold mb-6 flex items-center space-x-3">
                <i data-feather="users" class="w-7 h-7 text-white"></i>
                <span>Step 2: Group Info</span>
            </h2>

            <div class="w-full">
                <label class="mb-2 block text-white font-semibold text-lg">
                    Group
                </label>

                <div class="flex items-center space-x-4 w-full bg-white rounded-xl px-5 py-4 shadow-lg">
                    <i data-feather="users" class="w-6 h-6 text-gray-600"></i>

                    <!-- Display only -->
                    <input id="groupInput" type="text" readonly
                        placeholder="Select Group"
                        class="w-full text-lg border-none focus:ring-0 focus:outline-none text-gray-900 bg-transparent">

                    <!-- Actual submitted value -->
                    <input type="hidden" name="group_id" id="groupIdInput" required>
                </div>

                <!-- Group selection buttons -->
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($groups as $group)
                        <button type="button"
                                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500 transition"
                                onclick="selectGroup('{{ $group->id }}', '{{ $group->name }}')">
                            {{ $group->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-between mt-8">
                <button type="button"
                    class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-semibold hover:bg-indigo-50 transition"
                    onclick="prevStep()">Previous</button>

                <button type="button"
                    class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-semibold hover:bg-indigo-50 transition"
                    onclick="nextStep()">Next</button>
            </div>
        </div>

        <!-- Step 3: Citation -->
        <div class="form-step hidden space-y-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center space-x-2">
                <i data-feather="file-text" class="w-6 h-6 text-white"></i>
                <span>Step 3: Submit Citation</span>
            </h2>

            <x-form.datepicker name="period" label="Period" placeholder="Select period (From – To)" />

            <div>
                <label class="mb-1">Citation (max 150 words)</label>
                <textarea id="citation" name="description" rows="6"
                    class="w-full p-3 rounded-lg text-black"
                    placeholder="Enter your citation (max: 150 words)" required></textarea>

                <p id="citationError" class="text-red-600 text-sm mt-1 hidden"></p>
                <p id="citationCount" class="text-gray-600 text-sm mt-1">0 / 150 words</p>
            </div>

            <div class="flex justify-between mt-4">
                <button type="button"
                    class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
                    onclick="prevStep()">Previous</button>

                <button type="submit"
                    class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50">
                    Submit
                </button>
            </div>
        </div>
    </form>

<script>
function selectGroup(id, name) {
    document.getElementById('groupIdInput').value = id;
    document.getElementById('groupInput').value = name;
}
</script>


<script>
    function selectGroup(id, name) {
        document.getElementById('groupIdInput').value = id;
        document.getElementById('groupNameInput').value = name;
        document.getElementById('groupInput').value = name;
    }
</script>


<script>
function selectGroup(id, name) {
    document.getElementById('groupInput').value = name; // visible
    document.getElementById('groupIdInput').value = id; // hidden for submission
}
</script>

<script>
function selectGroup(id, name) {
    document.getElementById('groupIdInput').value = id;  // Hidden input for submission
    document.getElementById('groupInput').value = name; // Display input for user
}
</script>




    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    feather.replace();

    // Multi-step form logic
    const steps = document.querySelectorAll('.form-step');
    let currentStep = 0;

    function showStep(index) {
        steps.forEach((step, i) => step.classList.toggle('hidden', i !== index));
    }

    function nextStep() {
        const inputs = steps[currentStep].querySelectorAll('input, textarea, select');
        for (let input of inputs) {
            if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }
        }
        if (currentStep < steps.length - 1) currentStep++;
        showStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function prevStep() {
        if (currentStep > 0) currentStep--;
        showStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    showStep(currentStep);

    // Prefill group from localStorage
    document.addEventListener("DOMContentLoaded", () => {
        const selectedGroup = localStorage.getItem("selectedGroup");
        if (selectedGroup) {
            const groupInput = document.getElementById("groupInput");
            const groupNameSpan = document.getElementById("selectedGroupName");
            const groupIdInput = document.getElementById("groupIdInput");

            if (groupInput) groupInput.value = selectedGroup;
            if (groupNameSpan) groupNameSpan.textContent = selectedGroup;
            if (groupIdInput) groupIdInput.value = localStorage.getItem("selectedGroupId");

            // Show Step 2 directly
            currentStep = 1;
            showStep(currentStep);
        }
    });

    // Citation word count validation
    const citationInput = document.getElementById('citation');
    const citationCount = document.getElementById('citationCount');
    const citationError = document.getElementById('citationError');
    const maxWords = 150;

    citationInput?.addEventListener('input', () => {
        let words = citationInput.value.trim().split(/\s+/).filter(w => w.length > 0);
        citationCount.textContent = `${words.length} / ${maxWords} words`;
        if (words.length > maxWords) {
            citationError.textContent = `You have exceeded the maximum of ${maxWords} words!`;
            citationError.classList.remove('hidden');
        } else citationError.classList.add('hidden');
    });

    document.querySelector('form')?.addEventListener('submit', function(e) {
        let words = citationInput.value.trim().split(/\s+/).filter(w => w.length > 0);
        if (words.length > maxWords) {
            e.preventDefault();
            citationError.textContent = `Cannot submit. Maximum ${maxWords} words allowed.`;
            citationError.classList.remove('hidden');
        }
    });
</script>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.form-step');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    let currentStep = 0; // Always start with Step 1

    function showStep(index) {
        steps.forEach((step, i) => step.classList.add('hidden'));
        steps[index].classList.remove('hidden');

        // Buttons logic
        if(prevBtn) prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
        if(nextBtn) nextBtn.style.display = index === steps.length - 1 ? 'none' : 'inline-block';
        if(submitBtn) submitBtn.style.display = index === steps.length - 1 ? 'inline-block' : 'none';
    }

    // Next / Previous navigation
    window.nextStep = () => { if(currentStep < steps.length - 1) currentStep++; showStep(currentStep); };
    window.prevStep = () => { if(currentStep > 0) currentStep--; showStep(currentStep); };

    // Initialize: show first step
    showStep(currentStep);

    // Optional: word count for citation
    const citationInput = document.getElementById('citation');
    const citationCount = document.getElementById('citationCount');
    const citationError = document.getElementById('citationError');
    const maxWords = 150;

    if(citationInput){
        citationInput.addEventListener('input', () => {
            let words = citationInput.value.trim().split(/\s+/).filter(w => w.length > 0);
            citationCount.textContent = `${words.length} / ${maxWords} words`;
            citationError.classList.toggle('hidden', words.length <= maxWords);
            if(words.length > maxWords) citationError.textContent = `Maximum ${maxWords} words exceeded!`;
        });

        // Prevent submission if too many words
        document.querySelector('form').addEventListener('submit', (e) => {
            let words = citationInput.value.trim().split(/\s+/).filter(w => w.length > 0);
            if(words.length > maxWords){
                e.preventDefault();
                citationError.textContent = `Cannot submit. Maximum ${maxWords} words allowed.`;
                citationError.classList.remove('hidden');
            }
        });
    }
});
</script>


<script>
function selectGroup(id, name) {
    document.getElementById('groupIdInput').value = id;
    document.getElementById('groupInput').value = name;
}

function nextStep() {
    const currentStep = document.querySelector('.form-step:not(.hidden)');

    // Check if current step is group selection and group is required
    const groupIdInput = document.getElementById('groupIdInput');
    if (groupIdInput && groupIdInput.hasAttribute('required') && !groupIdInput.value) {
        alert('Please select a group before continuing.');
        return;
    }

    currentStep.classList.add('hidden');
    currentStep.nextElementSibling.classList.remove('hidden');
}

function prevStep() {
    const currentStep = document.querySelector('.form-step:not(.hidden)');
    currentStep.classList.add('hidden');
    currentStep.previousElementSibling.classList.remove('hidden');
}
</script>


@endpush
