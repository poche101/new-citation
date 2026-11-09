@extends('layouts.app')

@section('title', 'Department Form - CELZ5 Citation')

@section('content')
    <div class="min-h-screen flex flex-col items-center py-10 px-4">

        <!-- Header -->
        <header class="text-center mb-6">
            <h1 class="text-5xl font-bold text-white drop-shadow-lg">CELZ5 Citation</h1>
            <p class="text-white/80 mt-2 text-lg">Explore our Departments & Groups</p>
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
                    <a href="{{ route('departments.index') }}" class="hover:underline flex items-center space-x-1">
                        <i data-feather="layers" class="w-4 h-4"></i>
                        <span>Departments</span>
                    </a>
                </li>
                <li><span class="mx-2">/</span></li>
                <li>
                    <span id="selectedDepartment" class="flex items-center space-x-1">
                        <i data-feather="briefcase" class="w-4 h-4"></i>
                        <span id="selectedDepartmentName">—</span>
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

            <!-- Progress -->
            <div class="mb-8 text-white">
                <p id="stepProgressLabel" class="mb-2 text-sm text-white/80">Step 1 of 5</p>
                <div class="h-1.5 w-full rounded bg-white/20">
                    <div id="stepProgressBar" class="h-full rounded bg-white transition-all" style="width: 20%"></div>
                </div>
            </div>

            <form id="multiStepForm" class="space-y-6 text-white" method="POST"
                action="{{ route('department-form.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Step 1: Personal Info -->
                <div class="form-step space-y-6"
                    data-has-error="{{ $errors->hasAny(['title', 'fullname', 'unit', 'designation', 'kingschat', 'phone']) ? 1 : 0 }}">
                    <h2 class="text-xl font-semibold mb-4 flex items-center space-x-2">
                        <i data-feather="user" class="w-6 h-6 text-white"></i>
                        <span>Step 1: Personal Information</span>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="title" label="Title" placeholder="Bro, Sis, Pastor..." icon="user"
                            required />
                        <x-form.input name="fullname" label="Full Name" placeholder="John Doe" icon="edit" required />
                        <x-form.input name="unit" label="Unit" placeholder="Enter Unit" icon="map-pin" required />
                        <x-form.input name="designation" label="Designation" placeholder="e.g. Leader" icon="briefcase"
                            required />
                        <x-form.input name="kingschat" label="Kingschat Handle" placeholder="@handle" icon="message-circle"
                            required />
                        <x-form.input name="phone" label="Phone Number" placeholder="+234 000 0000" icon="phone"
                            required />
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="button"
                            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50"
                            onclick="nextStep()">Next</button>
                    </div>
                </div>

                <!-- Step 2: Department -->
                <div class="form-step hidden space-y-10 w-full"
                    data-has-error="{{ $errors->has('department') ? 1 : 0 }}">
                    <h2 class="text-2xl font-semibold mb-6 flex items-center space-x-3">
                        <i data-feather="layers" class="w-7 h-7 text-white"></i>
                        <span>Step 2: Department</span>
                    </h2>

                    <div class="w-full">
                        <label class="mb-2 block text-white font-semibold text-lg">
                            Department
                        </label>

                        <div class="flex items-center space-x-4 w-full bg-white rounded-xl px-5 py-4 shadow-lg">
                            <i data-feather="briefcase" class="w-6 h-6 text-gray-600"></i>

                            <input id="departmentInput" type="text" name="department" readonly required
                                value="{{ old('department') }}"
                                data-missing-message="Please choose a department first."
                                placeholder="Select Department"
                                class="w-full text-lg border-none focus:ring-0 focus:outline-none text-gray-900 bg-transparent">
                        </div>

                        <p class="mt-2 text-sm text-white/80">
                            Wrong department?
                            <a href="{{ route('departments.index') }}" class="underline hover:text-white">Choose a different one</a>
                        </p>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button"
                            class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-semibold hover:bg-indigo-50 transition"
                            onclick="prevStep()">
                            Previous
                        </button>

                        <button type="button"
                            class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-semibold hover:bg-indigo-50 transition"
                            onclick="nextStep()">
                            Next
                        </button>
                    </div>
                </div>

                <!-- Steps 3-5: personal submission, unit/department submission, uploads -->
                @include('partials.citation-submission-steps', [
                    'unitLabel' => 'Unit / Department',
                    'personalCitationField' => 'citation',
                ])

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @include('partials.citation-form-scripts')

    <script>
        // Pre-fill the department chosen on the Departments page (or passed in the URL)
        (function () {
            const input = document.getElementById('departmentInput');
            const crumb = document.getElementById('selectedDepartmentName');

            let department = input.value.trim(); // kept by the server after a failed submit
            if (!department) {
                department = new URLSearchParams(window.location.search).get('department') || '';
            }
            if (!department) {
                try { department = localStorage.getItem('selectedDepartment') || ''; } catch (e) {}
            }

            if (department) {
                input.value = department;
                crumb.textContent = department;
            }
        })();
    </script>
@endpush
