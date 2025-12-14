@extends('layouts.app')

@section('title', 'Departments - CELZ5 Citation')

@section('content')
<section class="w-full py-16 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
    <div class="w-full px-6">
        <div class="glass p-12 rounded-3xl shadow-2xl backdrop-blur-xl">
            <h2 class="text-3xl text-white font-semibold mb-12 text-center flex items-center justify-center space-x-3">
                <i data-feather="layers" class="w-7 h-7 text-white"></i>
                <span>Departments</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach ($departments as $department)
                    <div class="p-6 rounded-2xl shadow-lg flex flex-col items-center text-center hover:scale-105 transition-transform cursor-pointer bg-white/10 backdrop-blur-md border border-white/20"
                        onclick="selectDepartment('{{ $department['name'] }}')">
                        <i data-feather="users" class="w-12 h-12 text-white mb-4"></i>
                        <h3 class="text-white font-semibold text-lg">{{ $department['name'] }}</h3>
                        <p class="text-white/70 text-sm mt-1">{{ $department['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    feather.replace();

    function selectDepartment(departmentName) {
        // Store the selected department in localStorage
        localStorage.setItem('selectedDepartment', departmentName);

        // Redirect to the department form page
        window.location.href = "{{ url('https://citations.christembassylz5.org/department-form') }}";
    }

    document.addEventListener('DOMContentLoaded', () => {
    const savedDept = localStorage.getItem('selectedDepartment');
    if (savedDept) {
        const breadcrumbSpan = document.getElementById('selectedDepartmentName');
        const deptInput = document.getElementById('departmentInput');
        if (breadcrumbSpan) breadcrumbSpan.textContent = savedDept;
        if (deptInput) deptInput.value = savedDept;
    }
});

</script>

@endpush
