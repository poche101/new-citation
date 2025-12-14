<br>
<br>
@extends('layouts.app')

@section('title', 'Groups - CELZ5 Citation')

@section('content')
<section class="w-full max-w-7xl px-4 mb-16">
    <h2 class="text-3xl text-white font-semibold mb-8 text-center flex items-center justify-center space-x-2">
        <i data-feather="users" class="w-6 h-6 text-white"></i>
        <span>Groups</span>
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="groupContainer">
        @php
            $groups = [
                'Lekki', 'Victoria Island', 'Alasia', 'Ikoyi 1 Group', 'Ikoyi 2 Group', 'Ajiwe', 'Obalende',
                'Mobil', 'Chevron', 'Onishon', 'Ajah', 'Kajola', 'Lekki Phase 1', 'Epe', 'Lagos Island',
                'Youth Group', 'Owode Badore', 'Free Trade Zone', 'Eputu', 'Ogombo', 'Abijo', 'Tedo'
            ];
        @endphp

        @foreach ($groups as $group)
            <div class="glass p-4 rounded-xl shadow-md flex flex-col items-center text-center hover:scale-105 transition-transform"
                onclick="goToGroupForm('{{ $group }}')">
                <i data-feather="map-pin" class="w-8 h-8 text-white mb-2"></i>
                <p class="text-white font-semibold">{{ $group }}</p>
                <p class="text-white/60 text-sm mt-1">Click to submit your citation</p>
            </div>
        @endforeach
    </div>
</section>
@endsection

@push('scripts')
<script>
    feather.replace();

    function goToGroupForm(groupName) {
        // Store the selected group in localStorage
        localStorage.setItem("selectedGroup", groupName);

        // Redirect to the group form route
        window.location.href = "{{ url('https://citations.christembassylz5.org/group-form') }}";
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Get the selected group from localStorage
    const selectedGroup = localStorage.getItem('selectedGroup');

    if (selectedGroup) {
        // Update breadcrumb
        const groupBreadcrumb = document.getElementById('selectedGroupName');
        if (groupBreadcrumb) groupBreadcrumb.textContent = selectedGroup;

        // Update group input field
        const groupInput = document.getElementById('groupInput');
        if (groupInput) groupInput.value = selectedGroup;

        // Clear localStorage after use
        localStorage.removeItem('selectedGroup');

        // Show first step of the multi-step form
        if (typeof currentStep !== 'undefined' && typeof showStep === 'function') {
            currentStep = 0; // first step index
            showStep(currentStep);
        }
    }
});
</script>

@endpush
