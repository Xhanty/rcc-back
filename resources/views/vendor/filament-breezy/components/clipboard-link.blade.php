@props([
    'data'
])
<button
    x-data="{ copied: false }"
    x-on:click.prevent="
        window.navigator.clipboard.writeText(@js($data));
        copied = true;
        $tooltip('{{ __('filament-breezy::default.clipboard.tooltip') }}');
        setTimeout(() => copied = false, 2000);
    "
    type="button"
    style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 500; border: 1px solid rgba(128, 128, 128, 0.2); background: transparent; cursor: pointer;"
>
    <x-filament::icon icon="heroicon-o-clipboard-document" style="width: 0.875rem; height: 0.875rem;" x-show="!copied" />
    <x-filament::icon icon="heroicon-s-check" style="width: 0.875rem; height: 0.875rem; color: rgb(16, 185, 129); display: none;" x-show="copied" />
    <span x-text="copied ? '¡Copiado!' : '{{ __('filament-breezy::default.clipboard.link') }}'">{{ __('filament-breezy::default.clipboard.link') }}</span>
</button>

