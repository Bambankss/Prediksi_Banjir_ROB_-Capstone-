<?php

namespace App\Console\Commands;

use App\Models\Kelurahan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ValidasiGeojson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geojson:validasi {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memvalidasi kelurahan.geojson terhadap 31 kode wilayah di database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('path') ?? public_path('data/kelurahan.geojson');

        $this->info("Memeriksa GeoJSON di: {$filePath}");

        if (!File::exists($filePath)) {
            $this->error("Berkas GeoJSON tidak ditemukan.");
            return Command::FAILURE;
        }

        $raw = File::get($filePath);
        $json = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Berkas bukan JSON valid: " . json_last_error_msg());
            return Command::FAILURE;
        }

        if (!isset($json['type']) || $json['type'] !== 'FeatureCollection') {
            $this->error("Tipe GeoJSON bukan 'FeatureCollection'.");
            return Command::FAILURE;
        }

        $features = $json['features'] ?? [];
        $featureCount = count($features);
        if ($featureCount !== 31) {
            $this->error("Jumlah fitur tidak 31 (ditemukan: {$featureCount}).");
            return Command::FAILURE;
        }

        $geoCodes = [];
        $duplicateCodes = [];
        $invalidGeometries = [];
        $missingProperties = [];

        foreach ($features as $index => $feat) {
            $geomType = $feat['geometry']['type'] ?? null;
            if (!in_array($geomType, ['Polygon', 'MultiPolygon'], true)) {
                $invalidGeometries[] = "Fitur index {$index} bertipe geometry '{$geomType}'";
            }

            $props = $feat['properties'] ?? [];
            if (!isset($props['kode_wilayah']) || empty($props['kode_wilayah'])) {
                $missingProperties[] = "Fitur index {$index} tidak memiliki properties.kode_wilayah";
                continue;
            }

            $code = trim($props['kode_wilayah']);
            if (in_array($code, $geoCodes, true)) {
                $duplicateCodes[] = $code;
            }
            $geoCodes[] = $code;
        }

        if (!empty($missingProperties)) {
            foreach ($missingProperties as $msg) {
                $this->error($msg);
            }
            return Command::FAILURE;
        }

        if (!empty($duplicateCodes)) {
            $this->error("Ditemukan kode_wilayah ganda: " . implode(', ', $duplicateCodes));
            return Command::FAILURE;
        }

        if (!empty($invalidGeometries)) {
            foreach ($invalidGeometries as $msg) {
                $this->error($msg);
            }
            return Command::FAILURE;
        }

        $dbCodes = Kelurahan::pluck('kode_wilayah')->all();
        $diffExcess = array_diff($geoCodes, $dbCodes);
        $diffMissing = array_diff($dbCodes, $geoCodes);

        if (!empty($diffExcess) || !empty($diffMissing)) {
            if (!empty($diffExcess)) {
                $this->error("Kode wilayah asing di GeoJSON (tidak ada di database): " . implode(', ', $diffExcess));
            }
            if (!empty($diffMissing)) {
                $this->error("Kode wilayah database yang hilang di GeoJSON: " . implode(', ', $diffMissing));
            }
            return Command::FAILURE;
        }

        $this->info("✅ Validasi GeoJSON sukses: 31 kode wilayah cocok persis dengan database, geometri valid.");
        return Command::SUCCESS;
    }
}

