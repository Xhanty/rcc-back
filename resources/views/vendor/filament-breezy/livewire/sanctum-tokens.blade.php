<x-filament::section
    :heading="__('filament-breezy::default.profile.sanctum.title')"
    :description="__('filament-breezy::default.profile.sanctum.description')"
    icon="heroicon-o-finger-print"
    class="shadow-xs border border-gray-200/80 dark:border-white/10"
>
    @if($plainTextToken)
        <div class="rounded-xl bg-amber-50/70 p-4 dark:bg-amber-950/30 space-y-3 ring-1 ring-inset ring-amber-600/20 mb-4">
            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">{{ __('filament-breezy::default.profile.sanctum.create.message') }}</p>
            <input type="text" disabled class="w-full py-1.5 px-3 rounded-lg bg-white border border-gray-200 font-mono text-xs text-gray-800 dark:bg-gray-800 dark:border-white/10 dark:text-gray-200" name="plain_text_token" value="{{ $plainTextToken }}" />
            <div class="flex items-center justify-between pt-1">
                <x-filament-breezy::clipboard-link :data="$plainTextToken" />
                <x-filament::button icon="heroicon-s-clipboard-document-check" size="sm" type="button" wire:click="$set('plainTextToken',null)">
                    {{ __('filament-breezy::default.profile.sanctum.copied.label') }}
                </x-filament::button>
            </div>
        </div>
    @endif
    <div style="display: {{ $plainTextToken ? 'none' : '' }}">
        {{ $this->table }}
    </div>
</x-filament::section>

