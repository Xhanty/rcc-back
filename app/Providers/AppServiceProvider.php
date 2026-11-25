<?php

namespace App\Providers;

use App\Livewire\Profile\CustomPersonalInfo;
use App\Livewire\Profile\MyMinistries;
use App\Livewire\Profile\MyPermissions;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(['livewire.temporary_file_upload.rules' => ['required', 'file', 'max:20480']]);

        Livewire::component('custom_personal_info', CustomPersonalInfo::class);
        Livewire::component('my_ministries', MyMinistries::class);
        Livewire::component('my_permissions', MyPermissions::class);

        Table::configureUsing(function (Table $table): void {
            $table->stackedOnMobile();
        });
    }
}
