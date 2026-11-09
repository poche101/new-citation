@props([
    'name',
    'label',
    'max',
    'rows' => 6,
    'hint' => null,
    'placeholder' => '',
    'required' => true,
])

{{-- A textarea with a live word counter. The counter and the over-limit check
     are driven by the data-word-limit attribute (see partials/citation-form-scripts). --}}
<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-white">
        {{ $label }}
        <span class="font-normal text-white/70">(max {{ number_format($max) }} words)</span>
        @if ($required)
            <span class="text-red-400">*</span>
        @endif
    </label>

    @if ($hint)
        <p class="mb-2 text-sm text-white/70">{{ $hint }}</p>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        data-word-limit="{{ $max }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full p-3 rounded-lg text-black']) }}
    >{{ old($name) }}</textarea>

    <p class="mt-1 text-sm text-white/70" data-word-counter="{{ $name }}">0 / {{ number_format($max) }} words</p>

    @error($name)
        <p class="mt-1 text-sm text-red-200">{{ $message }}</p>
    @enderror
</div>
