<?php

namespace App\Filament\ImageProviders;

use App\Models\Banner;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;
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
                $imagePaths = $this->resolveBannerImages(Banner::SECTION_ADMIN_LOGIN);

                // Si no hay activos en admin_login, usar las imágenes activas de home como respaldo del backend
                if ($imagePaths->isEmpty()) {
                    $imagePaths = $this->resolveBannerImages(Banner::SECTION_HOME);
                }

                if ($imagePaths->isNotEmpty()) {
                    $selectedPath = $imagePaths->random();
                    $url = str_starts_with($selectedPath, 'http://') || str_starts_with($selectedPath, 'https://')
                        ? $selectedPath
                        : '/storage/' . ltrim($selectedPath, '/');

                    return new Image('url("' . $url . '")');
                }
            }
        } catch (Throwable) {
            // Ignorar errores durante migraciones iniciales
        }

        return new Image('linear-gradient(135deg, #111827 0%, #1f2937 100%)');
    }

    /**
     * Obtiene las rutas de imágenes válidas (excluyendo videos) de los banners activos de una sección.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function resolveBannerImages(string $section)
    {
        return Banner::query()
            ->active()
            ->forSection($section)
            ->orderBy('sort_order')
            ->get()
            ->flatMap(fn (Banner $banner): array => $banner->getImagePaths())
            ->filter(fn (string $path): bool => ! Banner::isVideoPath($path) && (
                str_starts_with($path, 'http://') ||
                str_starts_with($path, 'https://') ||
                Storage::disk('public')->exists($path)
            ))
            ->values();
    }
}
