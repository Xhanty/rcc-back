<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Http;
use Throwable;

class SocialMediaItem extends Model
{
    use HasFactory, SoftDeletes;

    public const PLATFORM_SPOTIFY = 'spotify';
    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_INSTAGRAM = 'instagram';
    public const PLATFORM_FACEBOOK = 'facebook';

    protected $fillable = [
        'platform',
        'title',
        'subtitle',
        'description',
        'url',
        'posts',
        'embed_url',
        'thumbnail_url',
        'author_name',
        'show_in_community',
        'show_in_footer',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'posts' => 'array',
            'show_in_community' => 'boolean',
            'show_in_footer' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Plataformas soportadas.
     *
     * @return array<string, string>
     */
    public static function platformOptions(): array
    {
        return [
            self::PLATFORM_SPOTIFY => 'Spotify',
            self::PLATFORM_YOUTUBE => 'YouTube',
            self::PLATFORM_INSTAGRAM => 'Instagram',
            self::PLATFORM_FACEBOOK => 'Facebook',
        ];
    }

    /**
     * Plataformas que permiten agregar publicaciones/videos/playlists para Medios Digitales.
     *
     * @return list<string>
     */
    public static function embeddablePlatforms(): array
    {
        return [
            self::PLATFORM_SPOTIFY,
            self::PLATFORM_YOUTUBE,
            self::PLATFORM_INSTAGRAM,
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::saving(function (SocialMediaItem $item): void {
            $platform = (string) $item->platform;
            $rawPosts = is_array($item->posts) ? $item->posts : [];
            $normalizedPosts = [];

            foreach ($rawPosts as $post) {
                if (! is_array($post) || blank($post['url'] ?? null)) {
                    continue;
                }

                $postUrl = trim((string) $post['url']);
                $meta = static::extractMetadataFromUrl($platform, $postUrl);

                $normalizedPosts[] = [
                    'url' => $postUrl,
                    'title' => filled($post['title'] ?? null) ? trim((string) $post['title']) : ($meta['title'] ?? null),
                    'description' => filled($post['description'] ?? null) ? trim((string) $post['description']) : null,
                    'embed_url' => $meta['embed_url'],
                    'thumbnail_url' => $meta['thumbnail_url'],
                    'author_name' => $meta['author_name'],
                    'content_type' => $meta['content_type'],
                ];
            }

            $item->posts = $normalizedPosts;

            // Sincronizar campos raíz con la primera publicación para retrocompatibilidad
            if (! empty($normalizedPosts)) {
                $first = $normalizedPosts[0];
                $item->title = $first['title'] ?? null;
                $item->description = $first['description'] ?? null;
                $item->embed_url = $first['embed_url'] ?? null;
                $item->thumbnail_url = $first['thumbnail_url'] ?? null;
                $item->author_name = $first['author_name'] ?? null;
            } elseif (filled($item->url)) {
                $meta = static::extractMetadataFromUrl($platform, (string) $item->url);
                $item->embed_url = $meta['embed_url'];
                $item->thumbnail_url = $meta['thumbnail_url'];
                $item->author_name = $meta['author_name'];
            } else {
                $item->embed_url = null;
                $item->thumbnail_url = null;
                $item->author_name = null;
            }
        });
    }

    /**
     * Extrae automáticamente embed_url, miniatura y título desde el enlace según la red social.
     *
     * @return array{embed_url: ?string, thumbnail_url: ?string, title: ?string, author_name: ?string, content_type: string}
     */
    public static function extractMetadataFromUrl(string $platform, string $url): array
    {
        $url = trim($url);
        $embedUrl = null;
        $thumbnailUrl = null;
        $title = null;
        $authorName = null;
        $contentType = 'profile_link';

        if ($platform === self::PLATFORM_YOUTUBE) {
            $videoId = static::extractYoutubeId($url);
            if ($videoId) {
                $contentType = 'video';
                $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                $thumbnailUrl = "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg";

                try {
                    $res = Http::timeout(4)->get('https://www.youtube.com/oembed', [
                        'url' => "https://www.youtube.com/watch?v={$videoId}",
                        'format' => 'json',
                    ]);

                    if ($res->successful()) {
                        $json = $res->json();
                        $title = $json['title'] ?? null;
                        $authorName = $json['author_name'] ?? null;
                        $thumbnailUrl = $json['thumbnail_url'] ?? $thumbnailUrl;
                    }
                } catch (Throwable) {
                    // Continuar con el thumbnail por defecto si falla oEmbed
                }
            }
        } elseif ($platform === self::PLATFORM_SPOTIFY) {
            if (preg_match('/open\.spotify\.com\/(?:intl-[a-zA-Z-]+\/)?(playlist|album|track|episode|show)\/([a-zA-Z0-9]+)/i', $url, $m)) {
                $type = strtolower($m[1]);
                $id = $m[2];
                $contentType = $type;
                $embedUrl = "https://open.spotify.com/embed/{$type}/{$id}?utm_source=generator";

                try {
                    $res = Http::timeout(4)->get('https://open.spotify.com/oembed', [
                        'url' => "https://open.spotify.com/{$type}/{$id}",
                    ]);

                    if ($res->successful()) {
                        $json = $res->json();
                        $title = $json['title'] ?? null;
                        $thumbnailUrl = $json['thumbnail_url'] ?? null;
                    }
                } catch (Throwable) {
                    // Continuar sin oEmbed
                }
            }
        } elseif ($platform === self::PLATFORM_INSTAGRAM) {
            if (preg_match('/instagram\.com\/(?:[a-zA-Z0-9_.]+\/)?(p|reel|reels|tv)\/([a-zA-Z0-9_-]+)/i', $url, $m)) {
                $rawType = strtolower($m[1]);
                $type = $rawType === 'reels' ? 'reel' : $rawType;
                $code = $m[2];
                $contentType = $type === 'p' ? 'post_carousel' : 'reel';
                $embedUrl = "https://www.instagram.com/{$type}/{$code}/embed";
            }
        }

        return [
            'embed_url' => $embedUrl,
            'thumbnail_url' => $thumbnailUrl,
            'title' => $title,
            'author_name' => $authorName,
            'content_type' => $contentType,
        ];
    }

    /**
     * Extrae el ID de 11 caracteres de cualquier formato de URL de YouTube.
     */
    public static function extractYoutubeId(string $url): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
