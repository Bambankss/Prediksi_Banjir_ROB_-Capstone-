<?php

namespace App\Console\Commands;

use App\Models\DataHarian;
use App\Models\StatusPrediksi;
use App\Services\PrediksiService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class HitungPrediksi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prediksi:hitung {--tanggal=} {--dari=} {--sampai=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung prediksi banjir ROB menggunakan layanan ML / stub';

    /**
     * Execute the console command.
     */
    public function handle(PrediksiService $service): int
    {
        $tanggalSingle = $this->option('tanggal');
        $dari = $this->option('dari');
        $sampai = $this->option('sampai');
        $force = (bool) $this->option('force');

        $datesToProcess = [];

        if ($tanggalSingle) {
            $datesToProcess[] = $tanggalSingle;
        } elseif ($dari && $sampai) {
            $period = CarbonPeriod::create($dari, $sampai);
            foreach ($period as $dt) {
                $datesToProcess[] = $dt->format('Y-m-d');
            }
        } else {
            // Ambil semua tanggal di data_harian yang lengkap (31 kelurahan)
            $modelVersi = env('MODEL_VERSI_AKTIF', 'stub-v0');

            $allDates = DataHarian::select('tanggal')
                ->groupBy('tanggal')
                ->havingRaw('COUNT(DISTINCT kelurahan_id) = 31')
                ->orderBy('tanggal', 'asc')
                ->pluck('tanggal')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->all();

            if ($force) {
                $datesToProcess = $allDates;
            } else {
                // Filter tanggal yang belum dihitung atau tidak berstatus berhasil
                $alreadySuccess = StatusPrediksi::where('model_versi', $modelVersi)
                    ->where('status', 'berhasil')
                    ->pluck('tanggal')
                    ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                    ->all();

                $datesToProcess = array_values(array_diff($allDates, $alreadySuccess));
            }
        }

        if (empty($datesToProcess)) {
            $this->info("Tidak ada tanggal yang perlu diproses.");
            return Command::SUCCESS;
        }

        $this->info("Memulai perhitungan prediksi untuk " . count($datesToProcess) . " tanggal...");

        $successCount = 0;
        $failCount = 0;

        $bar = $this->output->createProgressBar(count($datesToProcess));
        $bar->start();

        foreach ($datesToProcess as $tgl) {
            $res = $service->hitungTanggal($tgl, $force);
            if ($res['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Hasil perhitungan:");
        $this->table(['Status', 'Jumlah Tanggal'], [
            ['Berhasil', $successCount],
            ['Gagal/Dilewati', $failCount],
            ['Total', count($datesToProcess)],
        ]);

        return $failCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}

