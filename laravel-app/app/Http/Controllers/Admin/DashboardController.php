<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataHarian;
use App\Models\Impor;
use App\Models\Kelurahan;
use App\Models\LogAktivitas;
use App\Models\StatusPrediksi;
use App\Services\PredictorClient;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PredictorClient $predictorClient): View
    {
        $modelVersi = env('MODEL_VERSI_AKTIF', 'stub-v0');

        // Statistik tanggal data harian
        $totalDates = DataHarian::select('tanggal')->distinct()->count('tanggal');
        $completeDates = DataHarian::select('tanggal')
            ->groupBy('tanggal')
            ->havingRaw('COUNT(DISTINCT kelurahan_id) = 31')
            ->get()
            ->count();

        // Status Prediksi untuk versi aktif
        $statusCounts = StatusPrediksi::where('model_versi', $modelVersi)
            ->select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        // Pemeriksaan kesehatan layanan ML
        $mlStatus = [
            'online' => false,
            'message' => 'Layanan ML tidak aktif',
            'details' => null,
        ];

        try {
            $health = $predictorClient->health();
            $mlStatus['online'] = ($health['status'] ?? '') === 'ok';
            $mlStatus['message'] = $mlStatus['online'] ? 'Layanan Terhubung' : 'Status Tidak OK';
            $mlStatus['details'] = $health;
        } catch (\Throwable $e) {
            $mlStatus['online'] = false;
            $mlStatus['message'] = 'Tidak Terhubung (' . $e->getMessage() . ')';
        }

        // Pemeriksaan GeoJSON
        $geojsonPath = public_path('data/kelurahan.geojson');
        $geojsonStatus = [
            'valid' => false,
            'message' => 'Berkas tidak ditemukan',
        ];

        if (File::exists($geojsonPath)) {
            $raw = File::get($geojsonPath);
            $json = json_decode($raw, true);
            if ($json && isset($json['features']) && count($json['features']) === 31) {
                $geojsonStatus['valid'] = true;
                $geojsonStatus['message'] = '31 Kelurahan Valid';
            } else {
                $geojsonStatus['message'] = 'Format atau jumlah fitur tidak sesuai';
            }
        }

        // Impor terakhir
        $latestImpor = Impor::latest()->take(5)->get();

        // Log aktivitas terakhir
        $latestLogs = LogAktivitas::latest('waktu')->take(6)->get();

        return view('admin.dashboard', [
            'totalDates' => $totalDates,
            'completeDates' => $completeDates,
            'statusCounts' => $statusCounts,
            'modelVersi' => $modelVersi,
            'isSimulasi' => str_starts_with($modelVersi, 'stub'),
            'mlStatus' => $mlStatus,
            'geojsonStatus' => $geojsonStatus,
            'latestImpor' => $latestImpor,
            'latestLogs' => $latestLogs,
        ]);
    }
}

