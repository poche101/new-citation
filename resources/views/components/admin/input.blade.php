@props([
    'name',
    'label',
    'type' => 'text',
    'required' => true,
    'value' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-semibold">
        {{ $label }}
        @unless ($required)
            <span class="font-normal text-slate-400">(optional)</span>
        @endunless
    </label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @error($name) aria-invalid="true" @enderror
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20 aria-[invalid=true]:border-red-500']) }}>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
