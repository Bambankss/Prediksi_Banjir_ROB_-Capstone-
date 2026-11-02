<?php

namespace App\Http\Controllers;

use App\Models\Kelurahan;
use App\Models\Prediksi;
use App\Models\StatusPrediksi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    protected function getActiveModelVersion(): string
    {
        return env('MODEL_VERSI_AKTIF', 'stub-v0');
    }

    /**
     * GET /data/kelurahan
     * Daftar 31 kelurahan beserta informasi statis
     */
    public function kelurahan(): JsonResponse
    {
        $kelurahans = Kelurahan::select([
            'kode_wilayah',
            'nama',
            'kecamatan',
            'elevasi',
            'jarak_pantai',
            'stasiun_pasut',
        ])->orderBy('nama')->get();

        return response()->json([
            'status' => 'ok',
            'data' => $kelurahans,
            'meta' => [
                'total' => $kelurahans->count(),
            ],
        ]);
    }

    /**
     * GET /data/tanggal-tersedia
     * Daftar tanggal yang memiliki 31 hasil prediksi lengkap untuk versi model aktif
     */
    public function tanggalTersedia(): JsonResponse
    {
        $modelVersi = $this->getActiveModelVersion();

        $dates = Prediksi::select('tanggal')
            ->where('model_versi', $modelVersi)
            ->groupBy('tanggal')
            ->havingRaw('COUNT(DISTINCT kelurahan_id) = 31')
            ->orderBy('tanggal', 'asc')
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->values();

        return response()->json([
            'status' => 'ok',
            'data' => $dates,
            'meta' => [
                'total' => count($dates),
                'model_versi' => $modelVersi,
                'simulasi' => str_starts_with($modelVersi, 'stub'),
                'jenis_data' => 'historis',
            ],
        ]);
    }

    /**
     * GET /data/prediksi?tanggal=
     * Hasil seluruh 31 kelurahan pada tanggal terpilih
     */
    public function prediksi(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');

        if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return response()->json([
                'status' => 'error',
                'kode' => 'PARAMETER_TIDAK_VALID',
                'pesan' => 'Parameter tanggal wajib diisi dengan format YYYY-MM-DD.',
                'detail' => [],
            ], 422);
        }

        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $tanggal);
            if ($parsedDate->format('Y-m-d') !== $tanggal) {
                throw new \Exception();
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'kode' => 'PARAMETER_TIDAK_VALID',
                'pesan' => 'Parameter tanggal bukan tanggal kalender yang valid.',
                'detail' => [],
            ], 422);
        }

        $modelVersi = $this->getActiveModelVersion();

        $predictions = Prediksi::with('kelurahan')
            ->where('tanggal', $tanggal)
            ->where('model_versi', $modelVersi)
            ->get();

        if ($predictions->count() < 31) {
            return response()->json([
                'status' => 'error',
                'kode' => 'TANGGAL_TIDAK_TERSEDIA',
                'pesan' => 'Data prediksi untuk tanggal ini belum tersedia.',
                'detail' => [],
            ], 404);
        }

        $statusRec = StatusPrediksi::where('tanggal', $tanggal)
            ->where('model_versi', $modelVersi)
            ->first();

        $isSimulasi = $predictions->first()->is_simulasi || str_starts_with($modelVersi, 'stub');
        $updatedAt = $statusRec?->dihitung_pada ?? $predictions->first()->updated_at;

        $formattedData = $predictions->map(function ($item) {
            return [
                'kode_wilayah' => $item->kelurahan->kode_wilayah,
                'nama' => $item->kelurahan->nama,
                'kecamatan' => $item->kelurahan->kecamatan,
                'tingkat' => $item->tingkat,
                'probabilitas' => round($item->probabilitas, 4),
                'fitur' => $item->fitur_json ?? [
                    'elevasi' => $item->kelurahan->elevasi,
                    'jarak_pantai' => $item->kelurahan->jarak_pantai,
                ],
            ];
        })->values();

        return response()->json([
            'status' => 'ok',
            'data' => $formattedData,
            'meta' => [
                'tanggal' => $tanggal,
                'model_versi' => $modelVersi,
                'simulasi' => (bool) $isSimulasi,
                'jenis_data' => 'historis',
                'status_hasil' => $statusRec?->status ?? 'berhasil',
                'diperbarui_pada' => Carbon::parse($updatedAt)->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /data/prediksi/{kode_wilayah}?tanggal=
     * Detail satu kelurahan pada tanggal terpilih
     */
    public function detailKelurahan(string $kode_wilayah, Request $request): JsonResponse
    {
        $kelurahan = Kelurahan::where('kode_wilayah', $kode_wilayah)->first();
        if (!$kelurahan) {
            return response()->json([
                'status' => 'error',
                'kode' => 'KELURAHAN_TIDAK_DITEMUKAN',
                'pesan' => "Kelurahan dengan kode wilayah '{$kode_wilayah}' tidak ditemukan.",
                'detail' => [],
            ], 404);
        }

        $tanggal = $request->query('tanggal');
        if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return response()->json([
                'status' => 'error',
                'kode' => 'PARAMETER_TIDAK_VALID',
                'pesan' => 'Parameter tanggal wajib diisi dengan format YYYY-MM-DD.',
                'detail' => [],
            ], 422);
        }

        $modelVersi = $this->getActiveModelVersion();

        $prediksi = Prediksi::where('kelurahan_id', $kelurahan->id)
            ->where('tanggal', $tanggal)
            ->where('model_versi', $modelVersi)
            ->first();

        if (!$prediksi) {
            return response()->json([
                'status' => 'error',
                'kode' => 'TANGGAL_TIDAK_TERSEDIA',
                'pesan' => 'Hasil prediksi untuk kelurahan dan tanggal ini belum tersedia.',
                'detail' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'kode_wilayah' => $kelurahan->kode_wilayah,
                'nama' => $kelurahan->nama,
                'kecamatan' => $kelurahan->kecamatan,
                'tingkat' => $prediksi->tingkat,
                'probabilitas' => round($prediksi->probabilitas, 4),
                'fitur' => $prediksi->fitur_json ?? [
                    'elevasi' => $kelurahan->elevasi,
                    'jarak_pantai' => $kelurahan->jarak_pantai,
                ],
            ],
            'meta' => [
                'tanggal' => $tanggal,
                'model_versi' => $modelVersi,
                'simulasi' => (bool) $prediksi->is_simulasi,
                'jenis_data' => 'historis',
                'diperbarui_pada' => $prediksi->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /data/ringkasan?tanggal=
     * Ringkasan jumlah kelurahan per tingkat risiko
     */
    public function ringkasan(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');
        if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return response()->json([
                'status' => 'error',
                'kode' => 'PARAMETER_TIDAK_VALID',
                'pesan' => 'Parameter tanggal wajib diisi dengan format YYYY-MM-DD.',
                'detail' => [],
            ], 422);
        }

        $modelVersi = $this->getActiveModelVersion();

        $counts = Prediksi::where('tanggal', $tanggal)
            ->where('model_versi', $modelVersi)
            ->select('tingkat', DB::raw('count(*) as total'))
            ->groupBy('tingkat')
            ->pluck('total', 'tingkat')
            ->all();

        $totalKelurahan = array_sum($counts);
        if ($totalKelurahan < 31) {
            return response()->json([
                'status' => 'error',
                'kode' => 'TANGGAL_TIDAK_TERSEDIA',
                'pesan' => 'Data prediksi untuk tanggal ini belum lengkap atau belum tersedia.',
                'detail' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'tinggi' => $counts['tinggi'] ?? 0,
                'sedang' => $counts['sedang'] ?? 0,
                'rendah' => $counts['rendah'] ?? 0,
                'total' => $totalKelurahan,
            ],
            'meta' => [
                'tanggal' => $tanggal,
                'model_versi' => $modelVersi,
                'simulasi' => str_starts_with($modelVersi, 'stub'),
                'jenis_data' => 'historis',
            ],
        ]);
    }
}

