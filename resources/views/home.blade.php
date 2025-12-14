@extends('layouts.app')

@section('title', 'CELZ5 Citation')

@section('content')

    <!-- Hero Section -->
    <section
        class="relative flex flex-col md:flex-row-reverse w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 overflow-hidden py-20 px-6 md:px-12 lg:px-24 items-center justify-center min-h-[60vh] text-white">
        <div class="md:w-1/2 flex justify-center items-center relative animate-float">
            <img src="{{ asset('images/hero.png') }}" alt="hero-img"
                class="w-80 md:w-96 lg:w-[450px] transform transition-transform duration-700 hover:scale-105">
        </div>
        <div class="relative z-10 md:w-1/2 overflow-hidden">
            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-6">
                CELZ5 <span class="text-yellow-300">Citation</span>
            </h1>
            <p class="text-xl md:text-2xl mb-4">
                Track, submit, and manage individual, departmental, and group citations with ease.
            </p>
            <p class="text-xl md:text-2xl mb-4">
                Enhance productivity and ensure contributions are recognized properly.
            </p>
        </div>
    </section>

    <!-- Departments Section -->
    <section class="w-full py-16 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
    <div class="w-full px-6">
        <div class="glass p-8 rounded-2xl shadow-2xl backdrop-blur-xl">
            <h2 class="text-2xl md:text-3xl text-white font-semibold mb-8 text-center flex items-center justify-center space-x-3">
                <i data-feather="layers" class="w-6 h-6 text-white"></i>
                <span>Departments</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($departments as $department)
                    <div class="p-4 rounded-xl shadow-md flex flex-col items-center text-center hover:scale-105 transition-transform cursor-pointer bg-white/10 backdrop-blur-md border border-white/20"
                         onclick="selectDepartment('{{ $department['name'] }}')">
                        <i data-feather="users" class="w-10 h-10 text-white mb-3"></i>
                        <h3 class="text-white font-semibold text-base md:text-lg">{{ $department['name'] }}</h3>
                        <p class="text-white/70 text-xs md:text-sm mt-1">{{ $department['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

    <!-- Groups Section -->
  <section class="w-full py-16 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
    <div class="w-full px-6">
        <div class="glass p-12 rounded-3xl shadow-2xl backdrop-blur-xl">
            <h2 class="text-3xl text-white font-semibold mb-12 text-center flex items-center justify-center space-x-3">
                <i data-feather="users" class="w-7 h-7 text-white"></i>
                <span>Groups</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach ($groups as $group)
                    <div class="p-6 rounded-2xl shadow-lg flex flex-col items-center text-center hover:scale-105 transition-transform cursor-pointer bg-white/10 backdrop-blur-md border border-white/20"
                        onclick="selectGroup('{{ $group }}')">
                        <i data-feather="users" class="w-12 h-12 text-white mb-4"></i>
                        <h3 class="text-white font-semibold text-lg">{{ $group }}</h3>
                        <p class="text-white/70 text-sm mt-1">Click to submit your citation</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>



@endsection

@push('scripts')
    <script>
        // Navigate to department or group form
        function goToForm(name, type) {
            const url = type === 'department' ?
                `{{ url('/dept-form') }}?department=${encodeURIComponent(name)}` :
                `{{ url('/group-form') }}?group=${encodeURIComponent(name)}`;
            window.location.href = url;
        }

        // Initialize Feather icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>


    {{-- DEPARTMENT BREADCRUMB --}}
    <script>
        function selectDepartment(departmentName) {
            // Store in localStorage
            localStorage.setItem('selectedDepartment', departmentName);

            // Redirect to the department form page
            window.location.href = '/department-form';
        }

        // On the department form page, prefill the breadcrumb and input
        document.addEventListener('DOMContentLoaded', () => {
            const savedDept = localStorage.getItem('selectedDepartment');
            if (savedDept) {
                // Update breadcrumb
                const breadcrumbSpan = document.getElementById('selectedDepartmentName');
                if (breadcrumbSpan) breadcrumbSpan.textContent = savedDept;

                // Update input field
                const deptInput = document.getElementById('departmentInput');
                if (deptInput) deptInput.value = savedDept;
            }
        });
    </script>

    @push('scripts')
<script>
    feather.replace();

   function selectGroup(groupName) {
    // Store selected group in localStorage
    localStorage.setItem('selectedGroup', groupName);

    // Redirect to the group-citations form page
    window.location.href = "{{ route('group-form.create') }}";
}

</script>
@endpush
@endpush
