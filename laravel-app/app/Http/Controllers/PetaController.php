<?php

namespace App\Http\Controllers;

use App\Models\Prediksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetaController extends Controller
{
    public function index(Request $request): View
    {
        $modelVersi = env('MODEL_VERSI_AKTIF', 'stub-v0');

        // Ambil daftar tanggal yang punya 31 prediksi lengkap (urut dari yang paling baru ke lama)
        $availableDates = Prediksi::select('tanggal')
            ->where('model_versi', $modelVersi)
            ->groupBy('tanggal')
            ->havingRaw('COUNT(DISTINCT kelurahan_id) = 31')
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->values();

        $selectedDate = $request->query('tanggal');
        if ($selectedDate && !$availableDates->contains($selectedDate)) {
            $selectedDate = null;
        }

        // Default ke tanggal terbaru bila ada
        if (!$selectedDate && $availableDates->isNotEmpty()) {
            $selectedDate = $availableDates->first();
        }

        return view('peta', [
            'availableDates' => $availableDates,
            'selectedDate' => $selectedDate,
            'modelVersi' => $modelVersi,
            'isSimulasi' => str_starts_with($modelVersi, 'stub'),
        ]);
    }
}

