@php
    $plugin = (function_exists('filament') && filament()->isServing()) ? \Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin::get() : null;
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $filters = $this->getFilters();
    $isCollapsible = $this->isCollapsible();
    $darkMode = $this->getDarkMode();
    $width = $this->getFilterFormWidth();
    $pollingInterval = $this->getPollingInterval();
    $chartId = $this->getChartId();
    $chartOptions = $this->getOptions();
    $loadingIndicator = $this->getLoadingIndicator();
    $contentHeight = $this->getContentHeight();
    $deferLoading = $this->getDeferLoading();
    $footer = $this->getFooter();
    $readyToLoad = $this->readyToLoad;
    $extraJsOptions = $this->extraJsOptions();
@endphp

<x-filament-widgets::widget
    class="fi-wi-chart filament-widgets-chart-widget filament-apex-charts-widget"
    x-data="{ showFilters: false }"
>
    <x-filament::section
        class="filament-apex-charts-section"
        :description="$subheading"
        :heading="$heading"
        :collapsible="$isCollapsible"
    >
        @if ($filters || method_exists($this, 'getFiltersSchema'))
            <x-slot name="afterHeader">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    @if ($filters)
                        <x-filament::input.wrapper
                            inline-prefix
                            wire:target="filter"
                            class="fi-wi-chart-filter"
                        >
                            <x-filament::input.select
                                inline-prefix
                                wire:model.live="filter"
                            >
                                @foreach ($filters as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    @endif

                    @if (method_exists($this, 'getFiltersSchema'))
                        <x-filament::icon-button
                            icon="heroicon-o-funnel"
                            color="gray"
                            x-on:click="showFilters = !showFilters"
                            x-bind:class="{ 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/50': showFilters }"
                            label="Filtros"
                            tooltip="Filtros"
                        />
                    @endif
                </div>
            </x-slot>
        @endif

        @if (method_exists($this, 'getFiltersSchema'))
            <div
                x-show="showFilters"
                x-collapse
                class="mb-4 p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10"
                style="display: none;"
            >
                {{ $this->getFiltersSchema() }}
            </div>
        @endif

        <x-filament-apex-charts::chart :$chartId :$chartOptions :$contentHeight :$pollingInterval :$loadingIndicator
            :$darkMode :$deferLoading :$readyToLoad :$extraJsOptions />

        @if ($footer)
            <div class="relative">
                {!! $footer !!}
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
