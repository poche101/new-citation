@props([
    'name',
    'label' => null,
    'placeholder' => '',
    'icon' => null,
    'required' => false,
])

<div class="flex flex-col space-y-1">
    @if($label)
        <label for="{{ $name }}" class="text-sm font-semibold text-white">
            {{ $label }} @if($required)<span class="text-red-400">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <i data-feather="{{ $icon }}"
               class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
        @endif

        <input
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full pl-10 pr-4 py-3 rounded-lg text-black focus:ring-2 focus:ring-indigo-500'
            ]) }}
        >
    </div>

    @error($name)
        <p class="text-red-400 text-xs">{{ $message }}</p>
    @enderror
</div>
