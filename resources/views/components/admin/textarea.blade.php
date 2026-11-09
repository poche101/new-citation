@props([
    'name',
    'label',
    'max' => null,
    'rows' => 6,
    'value' => null,
    'required' => true,
])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-semibold">
        {{ $label }}
        @if ($max)
            <span class="font-normal text-slate-400">(up to {{ number_format($max) }} words)</span>
        @endif
        @unless ($required)
            <span class="font-normal text-slate-400">(optional)</span>
        @endunless
    </label>

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
        @if ($required) required @endif
        @if ($max) data-word-limit="{{ $max }}" @endif
        @error($name) aria-invalid="true" @enderror
        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm leading-relaxed focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20 aria-[invalid=true]:border-red-500">{{ old($name, $value) }}</textarea>

    @if ($max)
        <p data-word-counter="{{ $name }}" class="mt-1 text-xs text-slate-500" aria-live="polite">0 / {{ number_format($max) }} words</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
