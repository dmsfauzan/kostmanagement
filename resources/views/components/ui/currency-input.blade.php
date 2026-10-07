@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'hint' => null,
    'placeholder' => '0',
])

@php
    $id = $attributes->get('id', $name);
    $initial = old($name, $value);
@endphp

<div
    x-data="{
        raw: '{{ $initial }}',
        display: '',
        format(v) {
            v = String(v).replace(/\D/g, '');
            return v === '' ? '' : 'Rp ' + new Intl.NumberFormat('id-ID').format(v);
        },
        update(e) {
            this.raw = e.target.value.replace(/\D/g, '');
            this.display = this.format(this.raw);
        },
        init() { this.display = this.format(this.raw); },
    }"
    {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}
>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if ($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-400">Rp</span>
        <input
            id="{{ $id }}"
            type="text"
            x-model="display"
            @input="update($event)"
            inputmode="numeric"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            class="block w-full rounded-lg border-gray-300 pl-10 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
        <input type="hidden" name="{{ $name }}" :value="raw">
    </div>

    @error($name)
        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
    @enderror

    @if ($hint)
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
</div>
