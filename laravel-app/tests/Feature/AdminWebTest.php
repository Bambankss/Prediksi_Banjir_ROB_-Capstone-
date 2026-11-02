<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\KelurahanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWebTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(KelurahanSeeder::class);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
        ]);
    }

    public function test_halaman_peta_publik_bisa_diakses(): void
    {
        $response = $this->get('/peta');
        $response->assertStatus(200);
        $response->assertSee('ROB Predictor');
        $response->assertSee('Peta Kerentanan');
    }

    public function test_halaman_tentang_data_bisa_diakses(): void
    {
        $response = $this->get('/tentang');
        $response->assertStatus(200);
        $response->assertSee('Tentang Data');
        $response->assertSee('Metodologi Prediksi');
        $response->assertSee('Disclaimer');
    }

    public function test_rute_admin_memerlukan_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');

        $response2 = $this->get('/admin/data');
        $response2->assertRedirect('/admin/login');
    }

    public function test_login_admin_berhasil(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_login_admin_gagal_jika_password_salah(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dashboard_admin_bisa_diakses_setelah_login(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Ringkasan Operasional');
        $response->assertSee('Kelola Data Harian');
    }

    public function test_halaman_kelola_data_admin_bisa_diakses(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/data');
        $response->assertStatus(200);
        $response->assertSee('Daftar Tanggal');
        $response->assertSee('Status Prediksi');
    }

    public function test_halaman_edit_data_tanggal_bisa_diakses(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/data/2026-01-01');
        $response->assertStatus(200);
        $response->assertSee('Edit Data Harian');
        $response->assertSee('2026-01-01');
    }

    public function test_halaman_riwayat_impor_bisa_diakses(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/impor');
        $response->assertStatus(200);
        $response->assertSee('Riwayat');
        $response->assertSee('Laporan Impor CSV');
    }

    public function test_halaman_log_aktivitas_bisa_diakses(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/aktivitas');
        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas Administrator');
    }

    public function test_logout_admin_berhasil(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/logout');
        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
