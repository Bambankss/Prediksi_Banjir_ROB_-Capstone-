<?php

namespace Tests\Feature;

use Database\Seeders\KelurahanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(KelurahanSeeder::class);
    }

    public function test_kelurahan_endpoint_returns_31_kelurahan(): void
    {
        $response = $this->getJson('/data/kelurahan');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonCount(31, 'data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'kode_wilayah',
                        'nama',
                        'kecamatan',
                        'elevasi',
                        'jarak_pantai',
                        'stasiun_pasut',
                    ]
                ],
                'meta' => ['total'],
            ]);
    }

    public function test_tanggal_tersedia_returns_array(): void
    {
        $response = $this->getJson('/data/tanggal-tersedia');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure([
                'status',
                'data',
                'meta' => ['total', 'model_versi', 'simulasi', 'jenis_data'],
            ]);
    }

    public function test_prediksi_rejects_missing_or_invalid_date_format(): void
    {
        // Parameter kosong
        $res1 = $this->getJson('/data/prediksi');
        $res1->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'kode' => 'PARAMETER_TIDAK_VALID',
            ]);

        // Format MM-DD-YYYY ditolak
        $res2 = $this->getJson('/data/prediksi?tanggal=01-15-2026');
        $res2->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'kode' => 'PARAMETER_TIDAK_VALID',
            ]);

        // Tanggal bukan kalender valid
        $res3 = $this->getJson('/data/prediksi?tanggal=2026-02-30');
        $res3->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'kode' => 'PARAMETER_TIDAK_VALID',
            ]);
    }

    public function test_prediksi_returns_404_for_unavailable_date(): void
    {
        $response = $this->getJson('/data/prediksi?tanggal=1990-01-01');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'kode' => 'TANGGAL_TIDAK_TERSEDIA',
            ]);
    }

    public function test_detail_kelurahan_returns_404_for_unknown_code(): void
    {
        $response = $this->getJson('/data/prediksi/99.99.99.9999?tanggal=2026-01-01');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'kode' => 'KELURAHAN_TIDAK_DITEMUKAN',
            ]);
    }
}
