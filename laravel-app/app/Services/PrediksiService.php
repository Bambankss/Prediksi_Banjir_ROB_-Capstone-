<?php

namespace App\Services;

use App\Models\DataHarian;
use App\Models\Kelurahan;
use App\Models\Prediksi;
use App\Models\StatusPrediksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrediksiService
{
    protected PredictorClient $client;
    protected string $modelVersiAktif;

    public function __construct(PredictorClient $client)
    {
        $this->client = $client;
        $this->modelVersiAktif = env('MODEL_VERSI_AKTIF', 'stub-v0');
    }

    /**
     * Hitung ulang prediksi untuk satu tanggal tertentu
     *
     * @param string $tanggal Format YYYY-MM-DD
     * @param bool $force Paksa hitung ulang meski status sedang menghitung
     * @return array [success => bool, message => string]
     */
    public function hitungTanggal(string $tanggal, bool $force = false): array
    {
        // 1. Ambil atau buat status prediksi
        $statusRecord = StatusPrediksi::firstOrCreate(
            ['tanggal' => $tanggal, 'model_versi' => $this->modelVersiAktif],
            ['status' => 'belum_dihitung', 'is_simulasi' => str_starts_with($this->modelVersiAktif, 'stub')]
        );

        if (!$force && $statusRecord->status === 'menghitung') {
            return [
                'success' => false,
                'message' => "Perhitungan untuk tanggal {$tanggal} sedang berjalan.",
            ];
        }

        // 2. Periksa kelengkapan data (harus tepat 31 kelurahan)
        $harianRows = DataHarian::with('kelurahan')
            ->where('tanggal', $tanggal)
            ->get();

        if ($harianRows->count() !== 31) {
            $statusRecord->update([
                'status' => 'kedaluwarsa',
                'pesan_galat' => "Data tidak lengkap: ditemukan {$harianRows->count()} dari 31 kelurahan.",
            ]);

            return [
                'success' => false,
                'message' => "Data tanggal {$tanggal} belum lengkap ({$harianRows->count()}/31 kelurahan).",
            ];
        }

        // 3. Set status menghitung
        $statusRecord->update([
            'status' => 'menghitung',
            'pesan_galat' => null,
        ]);

        // 4. Susun 31 baris masukan
        $featuresMap = [];
        $rowsToSend = [];

        foreach ($harianRows as $harian) {
            $kel = $harian->kelurahan;
            if (!$kel) {
                continue;
            }

            $featurePayload = [
                'kode_wilayah' => $kel->kode_wilayah,
                'elevasi' => (float) $kel->elevasi,
                'jarak_pantai' => (float) $kel->jarak_pantai,
                'curah_hujan' => (float) $harian->curah_hujan,
                'pasang_tertinggi' => (float) $harian->pasang_tertinggi,
                'gelombang_tertinggi' => (float) $harian->gelombang_tertinggi,
            ];

            $featuresMap[$kel->kode_wilayah] = [
                'kelurahan_id' => $kel->id,
                'snapshot' => $featurePayload,
            ];

            $rowsToSend[] = $featurePayload;
        }

        if (count($rowsToSend) !== 31) {
            $statusRecord->update([
                'status' => 'gagal',
                'pesan_galat' => 'Sebagian relasi kelurahan tidak ditemukan.',
            ]);
            return [
                'success' => false,
                'message' => 'Sebagian data kelurahan tidak valid.',
            ];
        }

        // 5. Panggil layanan prediksi
        try {
            $response = $this->client->predict($rowsToSend);
        } catch (\Throwable $e) {
            Log::error("Prediksi gagal untuk {$tanggal}: " . $e->getMessage());

            $statusRecord->update([
                'status' => 'gagal',
                'pesan_galat' => 'Layanan prediksi tidak tersedia atau waktu habis.',
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghubungi layanan prediksi: ' . $e->getMessage(),
            ];
        }

        // 6. Validasi respons secara ketat
        $modelVersiResp = $response['model_versi'] ?? null;
        if ($modelVersiResp !== $this->modelVersiAktif) {
            $statusRecord->update([
                'status' => 'gagal',
                'pesan_galat' => "Versi model respons ('{$modelVersiResp}') tidak sesuai versi aktif ('{$this->modelVersiAktif}').",
            ]);
            return [
                'success' => false,
                'message' => 'Versi model pada respons tidak sesuai.',
            ];
        }

        $hasilList = $response['hasil'] ?? [];
        if (!is_array($hasilList) || count($hasilList) !== 31) {
            $statusRecord->update([
                'status' => 'gagal',
                'pesan_galat' => "Jumlah hasil prediksi tidak 31 (diterima: " . count($hasilList) . ").",
            ]);
            return [
                'success' => false,
                'message' => 'Jumlah hasil prediksi tidak lengkap.',
            ];
        }

        $simulasi = (bool) ($response['simulasi'] ?? false);
        $predictionsToSave = [];
        $seenCodes = [];

        foreach ($hasilList as $item) {
            $kode = $item['kode_wilayah'] ?? null;
            if (!$kode || !isset($featuresMap[$kode])) {
                $statusRecord->update([
                    'status' => 'gagal',
                    'pesan_galat' => "Kode wilayah asing atau hilang di respons: '{$kode}'.",
                ]);
                return [
                    'success' => false,
                    'message' => "Kode wilayah respons tidak valid: {$kode}",
                ];
            }

            if (isset($seenCodes[$kode])) {
                $statusRecord->update([
                    'status' => 'gagal',
                    'pesan_galat' => "Kode wilayah ganda di respons: '{$kode}'.",
                ]);
                return [
                    'success' => false,
                    'message' => "Respons memiliki kode wilayah duplikat: {$kode}",
                ];
            }
            $seenCodes[$kode] = true;

            $prob = $item['probabilitas'] ?? null;
            $tingkat = $item['tingkat'] ?? null;

            if (!is_numeric($prob) || $prob < 0 || $prob > 1) {
                $statusRecord->update([
                    'status' => 'gagal',
                    'pesan_galat' => "Nilai probabilitas tidak valid untuk kode {$kode}.",
                ]);
                return [
                    'success' => false,
                    'message' => "Nilai probabilitas di luar rentang 0-1.",
                ];
            }

            if (!in_array($tingkat, ['rendah', 'sedang', 'tinggi'], true)) {
                $statusRecord->update([
                    'status' => 'gagal',
                    'pesan_galat' => "Nilai tingkat '{$tingkat}' tidak valid.",
                ]);
                return [
                    'success' => false,
                    'message' => "Nilai tingkat tidak valid: {$tingkat}",
                ];
            }

            $predictionsToSave[] = [
                'kelurahan_id' => $featuresMap[$kode]['kelurahan_id'],
                'tanggal' => $tanggal,
                'model_versi' => $this->modelVersiAktif,
                'probabilitas' => (float) $prob,
                'tingkat' => $tingkat,
                'fitur_json' => json_encode($featuresMap[$kode]['snapshot']),
                'is_simulasi' => $simulasi,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        // 7. Simpan dalam SATU transaksi database
        DB::beginTransaction();
        try {
            Prediksi::upsert(
                $predictionsToSave,
                ['kelurahan_id', 'tanggal', 'model_versi'],
                ['probabilitas', 'tingkat', 'fitur_json', 'is_simulasi', 'updated_at']
            );

            $statusRecord->update([
                'status' => 'berhasil',
                'is_simulasi' => $simulasi,
                'jumlah_hasil' => 31,
                'dihitung_pada' => now(),
                'pesan_galat' => null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan prediksi {$tanggal}: " . $e->getMessage());

            $statusRecord->update([
                'status' => 'gagal',
                'pesan_galat' => 'Galat database saat menyimpan hasil prediksi.',
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menyimpan hasil ke database.',
            ];
        }

        return [
            'success' => true,
            'message' => "Prediksi untuk tanggal {$tanggal} berhasil dihitung dan disimpan (31 kelurahan).",
        ];
    }
}

