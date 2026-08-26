<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        <p style="font-size: 0.8125rem; opacity: 0.75; line-height: 1.4;">
            {{ __('filament-breezy::default.profile.browser_sessions.content') }}
        </p>

        @if (count($data) > 0)
            <div style="display: flex; flex-direction: column; gap: 0.625rem; margin-top: 0.5rem;">
                @foreach ($data as $session)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(128, 128, 128, 0.15); background-color: rgba(128, 128, 128, 0.03);">
                        <div style="display: flex; align-items: center; gap: 0.875rem;">
                            <div style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.625rem; background-color: rgba(128, 128, 128, 0.1); flex-shrink: 0;">
                                @if ($session->device['desktop'])
                                    <x-filament::icon
                                        icon="heroicon-o-computer-desktop"
                                        style="width: 1.25rem; height: 1.25rem;"
                                    />
                                @else
                                    <x-filament::icon
                                        icon="heroicon-o-device-phone-mobile"
                                        style="width: 1.25rem; height: 1.25rem;"
                                    />
                                @endif
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.125rem;">
                                <div style="font-size: 0.875rem; font-weight: 600;">
                                    {{ $session->device['platform'] ?: __('Unknown') }} &middot; {{ $session->device['browser'] ?: __('Unknown') }}
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; opacity: 0.7; font-family: monospace;">
                                    <span>IP: {{ $session->ip_address }}</span>
                                    @if (!$session->is_current_device)
                                        <span style="font-family: sans-serif;">&bull;</span>
                                        <span style="font-family: sans-serif;">{{ __('filament-breezy::default.profile.browser_sessions.last_active') }} {{ $session->last_active }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            @if ($session->is_current_device)
                                <x-filament::badge color="success" size="sm">
                                    {{ __('filament-breezy::default.profile.browser_sessions.device') }}
                                </x-filament::badge>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-dynamic-component>


