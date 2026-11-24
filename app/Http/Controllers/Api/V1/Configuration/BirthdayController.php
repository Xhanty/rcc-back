<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BirthdayController extends Controller
{
    use ApiResponse;

    /**
     * Devuelve los cumpleañeros del mes actual (solo nombre y día/mes, sin datos privados ni imágenes).
     */
    public function index(): JsonResponse
    {
        $now = Carbon::now();
        $todayDay = (int) $now->day;
        $todayMonth = (int) $now->month;

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

        $rows = DB::select(
            'SELECT asistente_id, nombre, dia_cumpleanos, mes_cumpleanos FROM vw_cumpleanos_mes_actual ORDER BY dia_cumpleanos ASC, nombre ASC'
        );

        $birthdays = collect($rows)->map(function ($row) use ($todayDay, $todayMonth, $monthNames): array {
            $day = (int) $row->dia_cumpleanos;
            $month = (int) $row->mes_cumpleanos;
            $monthName = $monthNames[$month] ?? '';

            return [
                'id' => (int) $row->asistente_id,
                'name' => $row->nombre,
                'day' => $day,
                'month' => $month,
                'date' => sprintf('%02d/%02d', $day, $month),
                'formatted_date' => "{$day} de {$monthName}",
                'is_today' => $day === $todayDay && $month === $todayMonth,
            ];
        })->values()->all();

        return $this->successResponse([
            'month' => $todayMonth,
            'month_name' => $monthNames[$todayMonth] ?? '',
            'total' => count($birthdays),
            'birthdays' => $birthdays,
        ]);
    }
}
