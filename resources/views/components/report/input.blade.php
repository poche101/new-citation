@props([
    'name',
    'label',
    'type' => 'text',
    'required' => true,
    'value' => null,
    'hint' => null,
    'placeholder' => '',
    'autocomplete' => 'off',
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-royal-900">
        {{ $label }}
        @unless ($required)
            <span class="font-normal text-slate-500">(optional)</span>
        @endunless
    </label>

    @if ($hint)
        <p id="{{ $name }}-hint" class="mb-1.5 text-sm text-slate-500">{{ $hint }}</p>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        autocomplete="{{ $autocomplete }}"
        @if ($required) required @endif
        @if ($hint) aria-describedby="{{ $name }}-hint" @endif
        @error($name) aria-invalid="true" @enderror
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-base text-ink shadow-sm placeholder:text-slate-400 focus:border-royal-500 focus:outline-none focus:ring-2 focus:ring-royal-500/30 aria-[invalid=true]:border-red-500']) }}>

    @error($name)
        <p class="mt-1.5 text-sm font-medium text-red-700">{{ $message }}</p>
    @enderror
</div>
