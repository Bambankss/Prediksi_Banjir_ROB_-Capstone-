<?php

namespace Tests\Feature;

use App\Models\DataHarian;
use App\Models\Kelurahan;
use App\Models\Prediksi;
use App\Models\StatusPrediksi;
use App\Services\PrediksiService;
use Database\Seeders\KelurahanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PrediksiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(KelurahanSeeder::class);
    }

    public function test_hitung_tanggal_gagal_jika_data_tidak_lengkap(): void
    {
        // Hanya buat 1 data harian (kurang dari 31)
        $kel = Kelurahan::first();
        DataHarian::create([
            'kelurahan_id' => $kel->id,
            'tanggal' => '2026-05-01',
            'curah_hujan' => 10,
            'pasang_tertinggi' => 0.8,
            'gelombang_tertinggi' => 0.5,
            'sumber' => 'manual',
        ]);

        $service = app(PrediksiService::class);
        $result = $service->hitungTanggal('2026-05-01');

        $this->assertFalse($result['success']);
        $status = StatusPrediksi::where('tanggal', '2026-05-01')->first();
        $this->assertEquals('kedaluwarsa', $status->status);
    }

    public function test_hitung_tanggal_berhasil_dengan_mock_http(): void
    {
        $kelurahans = Kelurahan::all();
        $this->assertCount(31, $kelurahans);

        // Buat 31 baris data harian
        $mockHasil = [];
        foreach ($kelurahans as $kel) {
            DataHarian::create([
                'kelurahan_id' => $kel->id,
                'tanggal' => '2026-05-01',
                'curah_hujan' => 5.0,
                'pasang_tertinggi' => 1.1,
                'gelombang_tertinggi' => 0.4,
                'sumber' => 'manual',
            ]);

            $mockHasil[] = [
                'kode_wilayah' => $kel->kode_wilayah,
                'probabilitas' => 0.75,
                'tingkat' => 'tinggi',
            ];
        }

        // Mock Predictor service response
        Http::fake([
            '*/predict' => Http::response([
                'model_versi' => 'stub-v0',
                'mode' => 'stub',
                'simulasi' => true,
                'hasil' => $mockHasil,
            ], 200),
        ]);

        $service = app(PrediksiService::class);
        $result = $service->hitungTanggal('2026-05-01');

        $this->assertTrue($result['success']);
        $this->assertDatabaseCount('prediksi', 31);

        $status = StatusPrediksi::where('tanggal', '2026-05-01')->first();
        $this->assertEquals('berhasil', $status->status);
        $this->assertTrue($status->is_simulasi);
        $this->assertEquals(31, $status->jumlah_hasil);
    }

    public function test_layanan_mati_tidak_menghapus_hasil_lama(): void
    {
        $kelurahans = Kelurahan::all();

        // 1. Simpan hasil lama
        foreach ($kelurahans as $kel) {
            DataHarian::create([
                'kelurahan_id' => $kel->id,
                'tanggal' => '2026-05-01',
                'curah_hujan' => 5.0,
                'pasang_tertinggi' => 1.1,
                'gelombang_tertinggi' => 0.4,
                'sumber' => 'manual',
            ]);

            Prediksi::create([
                'kelurahan_id' => $kel->id,
                'tanggal' => '2026-05-01',
                'model_versi' => 'stub-v0',
                'probabilitas' => 0.30,
                'tingkat' => 'rendah',
                'fitur_json' => ['elevasi' => 1.0],
                'is_simulasi' => true,
            ]);
        }

        StatusPrediksi::create([
            'tanggal' => '2026-05-01',
            'model_versi' => 'stub-v0',
            'status' => 'berhasil',
            'is_simulasi' => true,
            'jumlah_hasil' => 31,
        ]);

        // 2. Mock service error (500)
        Http::fake([
            '*/predict' => Http::response(['error' => 'server down'], 500),
        ]);

        $service = app(PrediksiService::class);
        $result = $service->hitungTanggal('2026-05-01', force: true);

        $this->assertFalse($result['success']);

        // Hasil lama harus tetap ada 31 baris dan tidak terhapus
        $this->assertDatabaseCount('prediksi', 31);
        $firstPred = Prediksi::where('tanggal', '2026-05-01')->first();
        $this->assertEquals('rendah', $firstPred->tingkat);

        // Status diupdate menjadi gagal
        $status = StatusPrediksi::where('tanggal', '2026-05-01')->first();
        $this->assertEquals('gagal', $status->status);
    }
}

