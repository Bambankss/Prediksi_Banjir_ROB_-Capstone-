<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AktivitasController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataHarianController;
use App\Http\Controllers\Admin\ImporController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\TentangController;
use Illuminate\Support\Facades\Route;

// Redirect root to /peta
Route::get('/', function () {
    return redirect('/peta');
});

// Halaman Publik
Route::get('/peta', [PetaController::class, 'index'])->name('peta');
Route::get('/tentang', [TentangController::class, 'index'])->name('tentang');

// API Data Publik (dengan Rate Limiting)
Route::prefix('data')->middleware('throttle:120,1')->group(function () {
    Route::get('/kelurahan', [DataController::class, 'kelurahan'])->name('data.kelurahan');
    Route::get('/tanggal-tersedia', [DataController::class, 'tanggalTersedia'])->name('data.tanggal-tersedia');
    Route::get('/prediksi', [DataController::class, 'prediksi'])->name('data.prediksi');
    Route::get('/prediksi/{kode_wilayah}', [DataController::class, 'detailKelurahan'])->name('data.prediksi.detail');
    Route::get('/ringkasan', [DataController::class, 'ringkasan'])->name('data.ringkasan');
});

// Admin Auth
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Panel (Protected by auth middleware)
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Pengelolaan Data Harian & Prediksi
    Route::get('/data', [DataHarianController::class, 'index'])->name('data.index');
    Route::get('/data/{tanggal}', [DataHarianController::class, 'edit'])->name('data.edit');
    Route::put('/data/{tanggal}', [DataHarianController::class, 'update'])->name('data.update');
    Route::delete('/data/{tanggal}', [DataHarianController::class, 'destroy'])->name('data.destroy');
    Route::post('/data/{tanggal}/hitung', [DataHarianController::class, 'hitung'])->name('data.hitung');

    // Riwayat Impor
    Route::get('/impor', [ImporController::class, 'index'])->name('impor.index');
    Route::get('/impor/{id}', [ImporController::class, 'show'])->name('impor.show');

    // Log Aktivitas
    Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index');
});
