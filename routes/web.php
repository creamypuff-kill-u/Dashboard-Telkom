<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Rute Admin CRUD: Hanya satu rute utama untuk manajemen data ---
    // Rute ini akan menangani semua bagian manajemen data (Orders, Jadwal, Funneling, dll.)
    // Parameter 'section' akan dilewatkan melalui URL query string (contoh: /admin/data-management?section=orders)
    Route::get('/admin/data-management', function () {
        return Inertia::render('Admin/DataManagement/Index');
    })->name('admin.data_management.index');

    Route::get('/admin/input-jadwal-teknisi', function () {
        return Inertia::render('Admin/DataManagement/InputJadwalTeknisi'); // Merender komponen khusus ini
    })->name('admin.input_jadwal_teknisi.index'); // Nama rute baru

    Route::get('/admin/Teknisi', function () {
        return Inertia::render('Admin/DataManagement/Teknisi'); // Merender komponen khusus ini
    })->name('admin.teknisi.index'); // Nama rute baru
    
    // --- Akhir Rute Admin CRUD ---

    // Hapus atau pastikan rute-rute yang tidak digunakan/tumpang tindih sudah dihapus/dikomentari
    // Contoh: Rute yang Anda tambahkan untuk '/admin/data-management/' yang memanggil InputJadwalTeknisi
    // Route::get('/admin/data-management/', function () {
    //     return Inertia::render('Admin/DataManagement/InputJadwalTeknisi');
    // })->name('admin.data_management.technician-schedule'); // Ini harus dihapus/dikomentari

    // Rute lama untuk /admin/orders juga harus dihapus/dikomentari jika sudah tidak dipakai
    // Route::get('/admin/orders', ...);
});

require __DIR__.'/auth.php';