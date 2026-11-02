<?php

namespace Database\Seeders;

use App\Models\Kelurahan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class KelurahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $possiblePaths = [
            base_path('../kelurahan.csv'),
            base_path('kelurahan.csv'),
            base_path('../data/kelurahan.csv'),
        ];

        $csvPath = null;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $csvPath = $path;
                break;
            }
        }

        if (!$csvPath) {
            throw new \RuntimeException("Berkas kelurahan.csv tidak ditemukan di jalur yang ditentukan.");
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Gagal membuka berkas kelurahan.csv.");
        }

        // Handle UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            throw new \RuntimeException("Berkas kelurahan.csv kosong atau header tidak valid.");
        }

        // Clean BOM or whitespace from header keys
        $header = array_map(function ($col) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col));
        }, $header);

        $rows = [];
        $kodeSeen = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) !== count($header)) {
                continue;
            }

            $row = array_combine($header, $data);
            $kode = trim($row['kode_wilayah'] ?? '');
            $nama = trim($row['kelurahan'] ?? '');
            $kecamatan = trim($row['kecamatan'] ?? '');
            $elevasi = trim($row['elevasi'] ?? '');
            $jarakPantai = trim($row['jarak_pantai'] ?? '');
            $stasiunPasut = trim($row['stasiun_pasut'] ?? '');

            // Validasi format kode wilayah: NN.NN.NN.NNNN
            if (!preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $kode)) {
                fclose($handle);
                throw new \RuntimeException("Format kode_wilayah tidak valid pada baris: {$kode}");
            }

            if (isset($kodeSeen[$kode])) {
                fclose($handle);
                throw new \RuntimeException("Duplikat kode_wilayah ditemukan: {$kode}");
            }
            $kodeSeen[$kode] = true;

            // Validasi stasiun pasut
            if (!in_array($stasiunPasut, ['Sunda Kelapa', 'Kolinlamil'], true)) {
                fclose($handle);
                throw new \RuntimeException("Nilai stasiun_pasut tidak valid ({$stasiunPasut}) pada kelurahan {$nama}");
            }

            // Validasi numerik
            if (!is_numeric($elevasi) || !is_numeric($jarakPantai)) {
                fclose($handle);
                throw new \RuntimeException("Nilai elevasi atau jarak_pantai bukan angka pada kelurahan {$nama}");
            }

            $rows[] = [
                'kode_wilayah' => $kode,
                'nama' => $nama,
                'kecamatan' => $kecamatan,
                'elevasi' => (float) $elevasi,
                'jarak_pantai' => (float) $jarakPantai,
                'stasiun_pasut' => $stasiunPasut,
            ];
        }

        fclose($handle);

        if (count($rows) !== 31) {
            throw new \RuntimeException("Jumlah kelurahan harus tepat 31, ditemukan: " . count($rows));
        }

        foreach ($rows as $item) {
            Kelurahan::updateOrCreate(
                ['kode_wilayah' => $item['kode_wilayah']],
                $item
            );
        }

        $this->command->info("Berhasil mengimpor 31 kelurahan dari kelurahan.csv.");
    }
}

