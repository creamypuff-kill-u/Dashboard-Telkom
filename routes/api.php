<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LookerDashboardDataController;

// Rute default untuk mendapatkan user yang sedang login (dari Breeze)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- MULAI BLOK RUTE API YANG TERLINDUNGI OLEH AUTENTIKASI SANCTUM ---
Route::middleware('auth:sanctum')->group(function () {

    // Rute untuk mengambil data dashboard (GET requests)
    Route::get('/dashboard-data/funneling', [LookerDashboardDataController::class, 'getFunnelingData']);
    Route::get('/dashboard-data/fallout', [LookerDashboardDataController::class, 'getFalloutData']);
    Route::get('/dashboard-data/ps-re-b2c', [LookerDashboardDataController::class, 'getPsReB2cData']);
    Route::get('/dashboard-data/technician-schedule', [LookerDashboardDataController::class, 'getTechnicianSchedule']);
    Route::get('/dashboard-data/main-table', [LookerDashboardDataController::class, 'getMainTableData']); // Ini untuk Tabel Order
    // Anda juga punya '/dashboard-data/tabel-order' yang memanggil 'getTabelOrder'.
    // Jika 'getTabelOrder' dan 'getMainTableData' mengambil data yang sama dari tab 'Tabel Order',
    // Anda mungkin hanya butuh satu rute saja, yaitu '/dashboard-data/main-table'.
    // Saya akan sertakan keduanya dulu, tapi pertimbangkan untuk menghapus salah satu jika duplikat.
    Route::get('/dashboard-data/tabel-order', [LookerDashboardDataController::class, 'getTabelOrder']); 

    // Rute untuk menambah dan mengupdate jadwal teknisi (POST/PUT requests)
    Route::post('/dashboard-data/technician-schedule', [LookerDashboardDataController::class, 'addTechnicianSchedule'])->name('dashboard-data.technician-schedule.store');
    Route::put('/dashboard-data/technician-schedule/{rowIndex}', [LookerDashboardDataController::class, 'updateTechnicianSchedule'])->name('dashboard-data.technician-schedule.update');
});
// --- AKHIR BLOK RUTE API YANG TERLINDUNGI ---