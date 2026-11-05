<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect('/admin');
});

// Rutas de descarga de reportes (protegidas por autenticación)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/reports/download/birthdays', [ReportController::class, 'downloadBirthdays'])
        ->name('reports.download.birthdays');
    Route::get('/admin/reports/download/pastoreo', [ReportController::class, 'downloadPastoreo'])
        ->name('reports.download.pastoreo');
});
