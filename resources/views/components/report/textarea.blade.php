@props([
    'name',
    'label',
    'max',
    'rows' => 8,
    'hint' => null,
    'placeholder' => '',
    'value' => null,
])

{{-- A textarea with a live word counter, driven by data-word-limit
     (see partials/report-form-scripts). --}}
<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-royal-900">
        {{ $label }}
        <span class="font-normal text-slate-500">(up to {{ number_format($max) }} words)</span>
    </label>

    @if ($hint)
        <p id="{{ $name }}-hint" class="mb-1.5 text-sm text-slate-500">{{ $hint }}</p>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        required
        data-word-limit="{{ $max }}"
        placeholder="{{ $placeholder }}"
        @if ($hint) aria-describedby="{{ $name }}-hint {{ $name }}-count" @else aria-describedby="{{ $name }}-count" @endif
        @error($name) aria-invalid="true" @enderror
        class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-base leading-relaxed text-ink shadow-sm placeholder:text-slate-400 focus:border-royal-500 focus:outline-none focus:ring-2 focus:ring-royal-500/30 aria-[invalid=true]:border-red-500">{{ old($name, $value) }}</textarea>

    <p id="{{ $name }}-count" data-word-counter="{{ $name }}" class="mt-1.5 text-sm text-slate-500" aria-live="polite">
        0 / {{ number_format($max) }} words
    </p>

    @error($name)
        <p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>
    @enderror
</div>
