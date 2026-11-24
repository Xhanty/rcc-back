<?php

namespace App\Filament\ImageProviders;

use App\Models\Banner;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use Throwable;

class AdminLoginBannerProvider implements ProvidesImages
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getImage(): Image
    {
        try {
            if (Schema::hasTable('banners')) {
                $imagePaths = Banner::query()
                    ->active()
                    ->forSection(Banner::SECTION_ADMIN_LOGIN)
                    ->orderBy('sort_order')
                    ->get()
                    ->flatMap(fn (Banner $banner): array => $banner->getImagePaths())
                    ->filter(fn (string $path): bool => Storage::disk('public')->exists($path))
                    ->values();

                if ($imagePaths->isNotEmpty()) {
                    $selectedPath = $imagePaths->random();
                    $url = Storage::disk('public')->url($selectedPath);

                    return new Image('url("' . $url . '")');
                }
            }
        } catch (Throwable) {
            // Fallback silencioso a las imágenes estáticas si falla la consulta
        }

        return MyImages::make()
            ->directory('backgrounds')
            ->getImage();
    }
}
