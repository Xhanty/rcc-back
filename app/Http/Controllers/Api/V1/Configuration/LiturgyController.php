<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class LiturgyController extends Controller
{
    use ApiResponse;

    protected const EVANGELIZO_BASE_URL = 'https://feed.evangelizo.org/v2/reader.php';
    protected const EVANGELIZO_SAINT_URL = 'https://feed.evangelizo.org/v2/display_saint.php';
    protected const VATICAN_GOSPEL_URL = 'https://www.vaticannews.va/es/evangelio-de-hoy.html';

    /**
     * Devuelve la Liturgia Diaria (Lecturas, Salmo, Evangelio, Meditación) y Santo(s) del Día.
     * Endpoint: GET /api/v1/configuration/liturgy/today?date=YYYY-MM-DD
     */
    public function today(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'date.date_format' => 'La fecha debe tener el formato YYYY-MM-DD.',
        ]);

        $targetDate = $request->query('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $request->query('date'), 'America/Bogota')
            : Carbon::now('America/Bogota');

        $dateStr = $targetDate->format('Y-m-d');
        $cacheKey = "liturgy_today_{$dateStr}";

        $data = Cache::remember($cacheKey, now()->addHours(12), function () use ($targetDate): array {
            return $this->fetchLiturgyData($targetDate);
        });

        return $this->successResponse($data);
    }

    /**
     * Consulta y estructura la liturgia y santos del día desde Evangelizo + Vatican News.
     *
     * @return array<string, mixed>
     */
    protected function fetchLiturgyData(Carbon $date): array
    {
        $ymd = $date->format('Ymd');

        $monthNames = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $formattedDate = ((int) $date->day) . ' de ' . ($monthNames[(int) $date->month] ?? '');

        $queries = [
            'liturgic_t' => ['type' => 'liturgic_t'],
            'feast'      => ['type' => 'feast'],
            'fr_lt'      => ['type' => 'reading_lt', 'content' => 'FR'],
            'fr_st'      => ['type' => 'reading_st', 'content' => 'FR'],
            'fr_text'    => ['type' => 'reading', 'content' => 'FR'],
            'ps_lt'      => ['type' => 'reading_lt', 'content' => 'PS'],
            'ps_st'      => ['type' => 'reading_st', 'content' => 'PS'],
            'ps_text'    => ['type' => 'reading', 'content' => 'PS'],
            'sr_lt'      => ['type' => 'reading_lt', 'content' => 'SR'],
            'sr_st'      => ['type' => 'reading_st', 'content' => 'SR'],
            'sr_text'    => ['type' => 'reading', 'content' => 'SR'],
            'gsp_lt'     => ['type' => 'reading_lt', 'content' => 'GSP'],
            'gsp_st'     => ['type' => 'reading_st', 'content' => 'GSP'],
            'gsp_text'   => ['type' => 'reading', 'content' => 'GSP'],
            'comment_t'  => ['type' => 'comment_t'],
            'comment_a'  => ['type' => 'comment_a'],
            'comment'    => ['type' => 'comment'],
            'audio'      => ['type' => 'audio'],
            'saint'      => ['type' => 'saint'],
        ];

        $raw = [];

        try {
            $responses = Http::pool(function (Pool $pool) use ($queries, $ymd) {
                foreach ($queries as $key => $params) {
                    $pool->as($key)
                        ->timeout(10)
                        ->get(self::EVANGELIZO_BASE_URL, array_merge([
                            'date' => $ymd,
                            'lang' => 'SP',
                        ], $params));
                }
            });

            foreach ($queries as $key => $params) {
                $response = $responses[$key] ?? null;
                $raw[$key] = ($response && method_exists($response, 'successful') && $response->successful())
                    ? $response->body()
                    : '';
            }
        } catch (Throwable) {
            foreach (array_keys($queries) as $key) {
                $raw[$key] = '';
            }
        }

        $liturgicalTitle = $this->cleanPlainText($raw['liturgic_t']) ?: 'Liturgia del Día';
        $liturgicalCycle = $this->detectLiturgicalCycle($liturgicalTitle);
        $liturgicalColor = $this->detectLiturgicalColor($liturgicalTitle, $this->cleanPlainText($raw['feast']));

        // Procesar lecturas
        $readings = [];

        $frContent = $this->cleanReadingHtml($raw['fr_text']);
        if ($frContent !== '') {
            $readings[] = [
                'type'      => 'first_reading',
                'label'     => 'Primera Lectura',
                'title'     => $this->extractBookTitle($raw['fr_lt'], 'Primera Lectura'),
                'reference' => $this->normalizeReference($raw['fr_st']),
                'content'   => $frContent,
            ];
        }

        $psContent = $this->cleanReadingHtml($raw['ps_text']);
        if ($psContent !== '') {
            $readings[] = [
                'type'      => 'psalm',
                'label'     => 'Salmo Responsorial',
                'title'     => $this->extractBookTitle($raw['ps_lt'], 'Salmo Responsorial'),
                'reference' => $this->normalizeReference($raw['ps_st']),
                'content'   => $psContent,
            ];
        }

        $srContent = $this->cleanReadingHtml($raw['sr_text']);
        if ($srContent !== '') {
            $readings[] = [
                'type'      => 'second_reading',
                'label'     => 'Segunda Lectura',
                'title'     => $this->extractBookTitle($raw['sr_lt'], 'Segunda Lectura'),
                'reference' => $this->normalizeReference($raw['sr_st']),
                'content'   => $srContent,
            ];
        }

        // Procesar Evangelio
        $gospelContent = $this->cleanReadingHtml($raw['gsp_text']);
        $gospelTitle = $this->extractBookTitle($raw['gsp_lt'], 'Santo Evangelio');
        $gospelReference = $this->normalizeReference($raw['gsp_st']);
        $gospelQuote = $this->extractGospelQuote($gospelContent, $this->cleanPlainText($raw['comment_t']));
        $audioUrl = $this->extractAudioUrl($raw['audio']);

        // Procesar Meditación
        $meditationTitle = trim($this->cleanPlainText($raw['comment_t']), " \t\n\r\0\x0B\"'“”«»");
        $meditationAuthor = $this->cleanPlainText($raw['comment_a']);
        $meditationContent = $this->cleanReadingHtml($raw['comment']);

        $meditation = ($meditationContent !== '') ? [
            'title'   => $meditationTitle ?: 'Meditación del Día',
            'author'  => $meditationAuthor ?: 'Comentario Pastoral',
            'content' => $meditationContent,
        ] : null;

        // Procesar Santo(s) del Día
        $saints = $this->parseSaints($raw['saint']);

        return [
            'date'             => $date->format('Y-m-d'),
            'formatted_date'   => $formattedDate,
            'liturgical_title' => $liturgicalTitle,
            'liturgical_cycle' => $liturgicalCycle,
            'liturgical_color' => $liturgicalColor,
            'gospel'           => [
                'title'     => $gospelTitle,
                'reference' => $gospelReference,
                'quote'     => $gospelQuote,
                'content'   => $gospelContent,
                'audio_url' => $audioUrl,
            ],
            'readings'         => $readings,
            'meditation'       => $meditation,
            'saints'           => $saints,
            'source_url'       => self::VATICAN_GOSPEL_URL,
        ];
    }

    /**
     * Limpia texto plano removiendo etiquetas HTML, scripts y anuncios de Evangelizo.
     */
    protected function cleanPlainText(?string $html): string
    {
        if (blank($html) || $this->isEvangelizoDocOrError($html)) {
            return '';
        }

        $clean = preg_replace('/<(script|style|ins|iframe)[^>]*>.*?<\/\1>/si', '', $html) ?? $html;
        $text = html_entity_decode(strip_tags($clean), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Limpia el HTML de una lectura o comentario eliminando anuncios (adsbygoogle), scripts y pies de página.
     */
    protected function cleanReadingHtml(?string $html): string
    {
        if (blank($html) || $this->isEvangelizoDocOrError($html)) {
            return '';
        }

        // Eliminar scripts, estilos, iframes y bloques de Google Ads
        $clean = preg_replace('/<(script|style|ins|iframe)[^>]*>.*?<\/\1>/si', '', $html) ?? $html;
        $clean = preg_replace('/<!--.*?-->/s', '', $clean) ?? $clean;

        // Reemplazar <br> por saltos de párrafo para estructurar limpio en <p>
        $clean = preg_replace('/<br\s*\/?>/i', "\n", $clean) ?? $clean;
        $text = html_entity_decode(strip_tags($clean), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $lines = array_values(array_filter(
            array_map(fn (string $line): string => trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $line) ?? $line), preg_split('/\r\n|\r|\n/', $text) ?: []),
            function (string $line): bool {
                if ($line === '') {
                    return false;
                }
                $lower = mb_strtolower($line);

                return ! str_contains($lower, 'adsbygoogle')
                    && ! str_contains($lower, 'evangelizo.org')
                    && ! str_contains($lower, 'evangeliodeldia.org')
                    && ! str_contains($lower, 'extraído de la biblia')
                    && ! str_contains($lower, 'extraido de la biblia')
                    && ! str_contains($lower, 'para recibir cada mañana');
            }
        ));

        if (empty($lines)) {
            return '';
        }

        return collect($lines)
            ->map(fn (string $paragraph): string => '<p>' . e($paragraph) . '</p>')
            ->implode('');
    }

    /**
     * Detecta si la respuesta de Evangelizo es su página de manual o un error de parámetros.
     */
    protected function isEvangelizoDocOrError(string $content): bool
    {
        return str_contains($content, 'Reader Evangelizo')
            || str_contains($content, 'wrong param')
            || str_contains($content, '<!DOCTYPE html>');
    }

    /**
     * Extrae el nombre del libro sin los números de capítulo/versículo.
     */
    protected function extractBookTitle(string $rawLongTitle, string $fallback): string
    {
        $plain = $this->cleanPlainText($rawLongTitle);
        if ($plain === '') {
            return $fallback;
        }

        // Quitar numeración final (ej. "Apocalipsis 10,8-11." -> "Lectura del libro del Apocalipsis" / "Apocalipsis")
        $book = trim(preg_replace('/\s+\d+[\d\s,.\-();a-zA-Z]*$/u', '', $plain) ?? $plain);

        if ($book === '') {
            return rtrim($plain, '.');
        }

        if (str_starts_with(mb_strtolower($book), 'salmo')) {
            if (preg_match('/(Salmo\s+\d+)/iu', $plain, $m)) {
                return $m[1];
            }
        }

        return $book;
    }

    /**
     * Normaliza la cita bíblica corta (ej. "Ps 119(118),14..." -> "Sal 119(118), 14...").
     */
    protected function normalizeReference(string $rawShortTitle): string
    {
        $plain = rtrim($this->cleanPlainText($rawShortTitle), '. ');
        if (str_starts_with($plain, 'Ps ')) {
            $plain = 'Sal ' . substr($plain, 3);
        }

        return $plain;
    }

    /**
     * Extrae una frase destacada del Evangelio o usa el título de la meditación.
     */
    protected function extractGospelQuote(string $gospelHtml, string $commentTitle): string
    {
        $cleanCommentTitle = trim($commentTitle, " \t\n\r\0\x0B\"'“”«»");
        if ($cleanCommentTitle !== '') {
            return $cleanCommentTitle;
        }

        $plain = strip_tags($gospelHtml);
        if (preg_match('/["“«]([^"”»]{15,140})["”»]/u', $plain, $matches)) {
            return trim($matches[1]);
        }

        return Str::limit(trim($plain), 110);
    }

    /**
     * Valida y extrae la URL de audio si Evangelizo devuelve un archivo válido.
     */
    protected function extractAudioUrl(string $rawAudio): ?string
    {
        $trimmed = trim($rawAudio);
        if ($trimmed === '' || $this->isEvangelizoDocOrError($trimmed)) {
            return null;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return $trimmed;
        }

        if (preg_match('/https?:\/\/[^\s"\'<>]+\.(mp3|m4a|ogg)/i', $trimmed, $m)) {
            return $m[0];
        }

        return null;
    }

    /**
     * Extrae los santos del día y consulta su biografía en español.
     *
     * @return list<array<string, mixed>>
     */
    protected function parseSaints(string $rawSaintHtml): array
    {
        if (blank($rawSaintHtml) || $this->isEvangelizoDocOrError($rawSaintHtml)) {
            return [];
        }

        $saintsList = [];

        // Extraer enlaces <a href="/v2/display_saint.php?id=...">Nombre del Santo</a>
        if (preg_match_all('/<a[^>]+href=["\']([^"\']*display_saint\.php\?id=([^"\'&]+)[^"\']*)["\'][^>]*>(.*?)<\/a>/si', $rawSaintHtml, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $saintId = trim(html_entity_decode($match[2]));
                $name = $this->cleanPlainText($match[3]);

                if ($name !== '') {
                    $saintsList[] = [
                        'id'   => $saintId,
                        'name' => $name,
                    ];
                }
            }
        }

        // Si no había enlaces pero sí texto plano
        if (empty($saintsList)) {
            $plainName = $this->cleanPlainText($rawSaintHtml);
            if ($plainName !== '') {
                $saintsList[] = [
                    'id'   => null,
                    'name' => $plainName,
                ];
            }
        }

        // Consultar las biografías de los primeros 3 santos con ID en paralelo
        $saintsWithId = array_filter($saintsList, fn (array $s): bool => ! empty($s['id']));
        $saintsToFetch = array_slice($saintsWithId, 0, 3);
        $biographies = [];

        if (! empty($saintsToFetch)) {
            try {
                $bioResponses = Http::pool(function (Pool $pool) use ($saintsToFetch) {
                    foreach ($saintsToFetch as $saint) {
                        $pool->as((string) $saint['id'])
                            ->timeout(8)
                            ->get(self::EVANGELIZO_SAINT_URL, [
                                'id'       => $saint['id'],
                                'language' => 'SP',
                            ]);
                    }
                });

                foreach ($saintsToFetch as $saint) {
                    $id = (string) $saint['id'];
                    $res = $bioResponses[$id] ?? null;
                    if ($res && method_exists($res, 'successful') && $res->successful()) {
                        $biographies[$id] = $this->parseSaintBioHtml($res->body(), $saint['name']);
                    }
                }
            } catch (Throwable) {
                // Continuar sin biografía si falla la red
            }
        }

        $result = [];
        foreach (array_slice($saintsList, 0, 4) as $saint) {
            $id = (string) ($saint['id'] ?? '');
            $bioData = $biographies[$id] ?? null;

            $result[] = [
                'name'             => $saint['name'],
                'celebration_type' => 'Memoria',
                'patronage'        => null,
                'summary'          => $bioData['summary'] ?? null,
                'biography'        => $bioData['biography'] ?? null,
                'image'            => $bioData['image'] ?? null,
                'prayer'           => null,
            ];
        }

        return $result;
    }

    /**
     * Extrae la biografía limpia y el resumen desde display_saint.php de Evangelizo.
     *
     * @return array{summary: ?string, biography: ?string, image: ?string}
     */
    protected function parseSaintBioHtml(string $html, string $saintName): array
    {
        $clean = preg_replace('/<(script|style|ins|iframe)[^>]*>.*?<\/\1>/si', '', $html) ?? $html;

        $image = null;
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $clean, $imgMatch)) {
            $src = trim($imgMatch[1]);
            if (! str_contains($src, 'logo') && ! str_contains($src, 'spacer') && ! str_contains($src, 'icon')) {
                $image = str_starts_with($src, 'http') ? $src : 'https://feed.evangelizo.org' . (str_starts_with($src, '/') ? '' : '/') . $src;
            }
        }

        $clean = preg_replace('/<br\s*\/?>/i', "\n", $clean) ?? $clean;
        $clean = preg_replace('/<\/(p|div|tr|td|h[1-6])>/i', "\n", $clean) ?? $clean;
        $text = html_entity_decode(strip_tags($clean), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $lines = array_values(array_filter(
            array_map(fn (string $line): string => trim(preg_replace('/\s+/u', ' ', $line) ?? $line), preg_split('/\r\n|\r|\n/', $text) ?: []),
            function (string $line) use ($saintName): bool {
                if ($line === '' || mb_strlen($line) < 4) {
                    return false;
                }
                $lower = mb_strtolower($line);
                if (
                    $lower === 'evangelizo'
                    || $lower === mb_strtolower($saintName)
                    || str_contains($lower, 'adsbygoogle')
                    || str_contains($lower, 'evangelizo.org')
                    || str_contains($lower, 'evangeliodeldia.org')
                    || str_contains($lower, 'cerrar ventana')
                ) {
                    return false;
                }

                return true;
            }
        ));

        if (empty($lines)) {
            return [
                'summary'   => null,
                'biography' => null,
                'image'     => $image,
            ];
        }

        $fullText = implode(' ', $lines);
        $summary = Str::limit($fullText, 200);
        $biography = collect($lines)
            ->map(fn (string $p): string => '<p>' . e($p) . '</p>')
            ->implode('');

        return [
            'summary'   => $summary,
            'biography' => $biography,
            'image'     => $image,
        ];
    }

    /**
     * Determina el tiempo/ciclo litúrgico a partir del título del día.
     */
    protected function detectLiturgicalCycle(string $liturgicalTitle): string
    {
        $lower = mb_strtolower($liturgicalTitle);

        return match (true) {
            str_contains($lower, 'adviento') => 'Tiempo de Adviento',
            str_contains($lower, 'navidad') || str_contains($lower, 'epifanía') => 'Tiempo de Navidad',
            str_contains($lower, 'cuaresma') || str_contains($lower, 'ceniza') => 'Tiempo de Cuaresma',
            str_contains($lower, 'semana santa') || str_contains($lower, 'pascua') || str_contains($lower, 'pentecostés') => 'Tiempo Pascual',
            default => 'Tiempo Ordinario',
        };
    }

    /**
     * Determina el color litúrgico según el tiempo o fiesta del día.
     */
    protected function detectLiturgicalColor(string $liturgicalTitle, string $feast): string
    {
        $combined = mb_strtolower($liturgicalTitle . ' ' . $feast);

        return match (true) {
            str_contains($combined, 'mártir') || str_contains($combined, 'pentecostés') || str_contains($combined, 'pasión') || str_contains($combined, 'viernes santo') || str_contains($combined, 'exaltación de la santa cruz') => 'Rojo',
            str_contains($combined, 'adviento') || str_contains($combined, 'cuaresma') || str_contains($combined, 'ceniza') || str_contains($combined, 'fieles difuntos') => 'Morado',
            str_contains($combined, 'navidad') || str_contains($combined, 'pascua') || str_contains($combined, 'resurrección') || str_contains($combined, 'virgen') || str_contains($combined, 'maría') || str_contains($combined, 'santo') || str_contains($combined, 'santa') || str_contains($combined, 'fiesta') || str_contains($combined, 'solemnidad') => 'Blanco',
            default => 'Verde',
        };
    }
}
