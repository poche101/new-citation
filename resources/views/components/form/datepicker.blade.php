@props([
    'name',
    'label' => null,
    'placeholder' => '',
])

<div class="flex flex-col space-y-2 w-full">
    @if($label)
        <label for="{{ $name }}" class="text-sm font-semibold text-white">
            {{ $label }}
        </label>
    @endif

    <div class="relative w-full">
        <i data-feather="calendar"
           class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>

        <input
            type="text"
            id="{{ $name }}"
            name="{{ $name }}"
            readonly
            placeholder="{{ $placeholder }}"
            value="{{ old($name) }}"
            {{ $attributes->merge([
                'class' =>
                    'w-full pl-12 pr-4 py-3 rounded-xl text-black bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none'
            ]) }}
        >
    </div>

    @error($name)
        <p class="text-red-400 text-xs">{{ $message }}</p>
    @enderror


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        flatpickr('#period', {
            mode: 'range',
            dateFormat: 'F j, Y'
        });

        feather.replace();
    });
</script>

</div>
