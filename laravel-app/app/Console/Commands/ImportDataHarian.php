<?php

namespace App\Console\Commands;

use App\Models\DataHarian;
use App\Models\Impor;
use App\Models\Kelurahan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportDataHarian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data-harian {file} {--sumber=CSV} {--prediksi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengimpor data harian dari berkas CSV dengan validasi ketat (all-or-nothing)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        // If not absolute path (Windows drive letter or leading slash)
        if (!preg_match('#^([a-zA-Z]:|[\\\\/])#', $filePath)) {
            $filePath = base_path($filePath);
        }

        if (!File::exists($filePath)) {
            $this->error("Berkas CSV tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $sumber = $this->option('sumber') ?: 'impor_csv';
        $fileName = basename($filePath);

        $this->info("Memulai impor dari: {$fileName}");

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->error("Gagal membuka berkas CSV.");
            return Command::FAILURE;
        }

        // Handle BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detect delimiter: comma or semicolon
        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) ? ';' : ',';
        rewind($handle);
        // Skip BOM again if rewind
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 2000, $delimiter);
        if (!$header) {
            fclose($handle);
            $this->error("Header CSV kosong atau tidak valid.");
            return Command::FAILURE;
        }

        // Clean header names
        $header = array_map(function ($col) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strtolower($col)));
        }, $header);

        $requiredCols = ['kode_wilayah', 'tanggal', 'curah_hujan', 'pasang_tertinggi', 'gelombang_tertinggi'];
        $missingCols = array_diff($requiredCols, $header);
        if (!empty($missingCols)) {
            fclose($handle);
            $this->error("Kolom wajib tidak lengkap. Kurang: " . implode(', ', $missingCols));
            return Command::FAILURE;
        }

        // Cache kelurahan map: kode_wilayah => id
        $kelurahanMap = Kelurahan::pluck('id', 'kode_wilayah')->all();

        $rowsToInsert = [];
        $errors = [];
        $rowNumber = 1; // 1 = header, data starts at 2
        $seenPairs = [];

        while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (count($data) === 1 && trim($data[0]) === '') {
                continue;
            }

            if (count($data) !== count($header)) {
                $errors[] = [
                    'baris' => $rowNumber,
                    'pesan' => "Jumlah kolom tidak sesuai header (" . count($data) . " vs " . count($header) . ")"
                ];
                continue;
            }

            $row = array_combine($header, $data);
            $kode = trim($row['kode_wilayah'] ?? '');
            $tanggal = trim($row['tanggal'] ?? '');
            $hujanRaw = trim($row['curah_hujan'] ?? '');
            $pasangRaw = trim($row['pasang_tertinggi'] ?? '');
            $gelombangRaw = trim($row['gelombang_tertinggi'] ?? '');

            // 1. Validasi kode_wilayah
            if (!preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $kode)) {
                $errors[] = [
                    'baris' => $rowNumber,
                    'pesan' => "Format kode_wilayah salah ({$kode}), harus NN.NN.NN.NNNN"
                ];
            } elseif (!isset($kelurahanMap[$kode])) {
                $errors[] = [
                    'baris' => $rowNumber,
                    'pesan' => "kode_wilayah {$kode} tidak terdaftar di master kelurahan"
                ];
            }

            // 2. Validasi tanggal format ISO YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                $errors[] = [
                    'baris' => $rowNumber,
                    'pesan' => "Format tanggal tidak valid ('{$tanggal}'). Gunakan format YYYY-MM-DD."
                ];
            } else {
                try {
                    $d = Carbon::createFromFormat('Y-m-d', $tanggal);
                    if ($d->format('Y-m-d') !== $tanggal) {
                        $errors[] = [
                            'baris' => $rowNumber,
                            'pesan' => "Tanggal '{$tanggal}' bukan tanggal kalender yang valid."
                        ];
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'baris' => $rowNumber,
                        'pesan' => "Tanggal '{$tanggal}' tidak valid."
                    ];
                }
            }

            // 3. Cek koma sebagai desimal
            foreach (['curah_hujan' => $hujanRaw, 'pasang_tertinggi' => $pasangRaw, 'gelombang_tertinggi' => $gelombangRaw] as $fName => $val) {
                if (strpos($val, ',') !== false) {
                    $errors[] = [
                        'baris' => $rowNumber,
                        'pesan' => "Nilai kolom '{$fName}' ({$val}) mengandung koma. Gunakan titik untuk desimal."
                    ];
                }
            }

            // 4. Validasi angka & batas nilai
            if (!is_numeric($hujanRaw)) {
                $errors[] = ['baris' => $rowNumber, 'pesan' => "curah_hujan harus berupa angka"];
            } elseif ((float) $hujanRaw < 0) {
                $errors[] = ['baris' => $rowNumber, 'pesan' => "curah_hujan tidak boleh negatif"];
            }

            if (!is_numeric($pasangRaw)) {
                $errors[] = ['baris' => $rowNumber, 'pesan' => "pasang_tertinggi harus berupa angka"];
            }

            if (!is_numeric($gelombangRaw)) {
                $errors[] = ['baris' => $rowNumber, 'pesan' => "gelombang_tertinggi harus berupa angka"];
            } elseif ((float) $gelombangRaw < 0) {
                $errors[] = ['baris' => $rowNumber, 'pesan' => "gelombang_tertinggi tidak boleh negatif"];
            }

            // 5. Cek duplikat di dalam berkas
            $pairKey = "{$kode}_{$tanggal}";
            if (isset($seenPairs[$pairKey])) {
                $errors[] = [
                    'baris' => $rowNumber,
                    'pesan' => "Kombinasi kode_wilayah {$kode} dan tanggal {$tanggal} duplikat di baris {$seenPairs[$pairKey]} dan {$rowNumber}"
                ];
            } else {
                $seenPairs[$pairKey] = $rowNumber;
            }

            if (empty($errors)) {
                $rowsToInsert[] = [
                    'kelurahan_id' => $kelurahanMap[$kode] ?? null,
                    'tanggal' => $tanggal,
                    'curah_hujan' => (float) $hujanRaw,
                    'pasang_tertinggi' => (float) $pasangRaw,
                    'gelombang_tertinggi' => (float) $gelombangRaw,
                    'sumber' => 'impor_csv',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        fclose($handle);

        $totalLines = $rowNumber - 1; // excluding header
        $dimulai = now();

        // Buat record impor
        $impor = Impor::create([
            'nama_berkas' => $fileName,
            'jenis' => 'data_harian',
            'sumber' => $sumber,
            'admin_id' => null,
            'status' => empty($errors) ? 'berhasil' : 'gagal',
            'jumlah_baris' => $totalLines,
            'jumlah_berhasil' => empty($errors) ? count($rowsToInsert) : 0,
            'jumlah_galat' => count($errors),
            'ringkasan_galat' => array_slice($errors, 0, 200),
            'dimulai_pada' => $dimulai,
            'selesai_pada' => now(),
        ]);

        if (!empty($errors)) {
            $this->error("❌ Impor GAGAL! Ditemukan " . count($errors) . " galat. Tidak ada baris yang disimpan (all-or-nothing).");
            $this->table(['Baris', 'Pesan Galat'], array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $this->warn("... dan " . (count($errors) - 10) . " galat lainnya (tersimpan di riwayat impor #{$impor->id}).");
            }
            return Command::FAILURE;
        }

        // Simpan dalam satu transaksi
        DB::beginTransaction();
        try {
            foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                foreach ($chunk as &$r) {
                    $r['impor_id'] = $impor->id;
                }
                DataHarian::upsert(
                    $chunk,
                    ['kelurahan_id', 'tanggal'],
                    ['curah_hujan', 'pasang_tertinggi', 'gelombang_tertinggi', 'sumber', 'impor_id', 'updated_at']
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $impor->update([
                'status' => 'gagal',
                'ringkasan_galat' => [['baris' => '-', 'pesan' => $e->getMessage()]],
                'jumlah_galat' => 1,
                'jumlah_berhasil' => 0,
            ]);
            $this->error("Galat database saat menyimpan: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("✅ Berhasil mengimpor " . count($rowsToInsert) . " baris data harian ke database (Impor ID #{$impor->id}).");

        return Command::SUCCESS;
    }
}
