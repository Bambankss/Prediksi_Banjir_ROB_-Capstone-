<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusPrediksi extends Model
{
    use HasFactory;

    protected $table = 'status_prediksi';

    protected $fillable = [
        'tanggal',
        'model_versi',
        'status',
        'is_simulasi',
        'jumlah_hasil',
        'dihitung_pada',
        'pesan_galat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'is_simulasi' => 'boolean',
            'jumlah_hasil' => 'integer',
            'dihitung_pada' => 'datetime',
        ];
    }
}

