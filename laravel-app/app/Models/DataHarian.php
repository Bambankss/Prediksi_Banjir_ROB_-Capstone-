<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataHarian extends Model
{
    use HasFactory;

    protected $table = 'data_harian';

    protected $fillable = [
        'kelurahan_id',
        'tanggal',
        'curah_hujan',
        'pasang_tertinggi',
        'gelombang_tertinggi',
        'sumber',
        'impor_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'curah_hujan' => 'float',
            'pasang_tertinggi' => 'float',
            'gelombang_tertinggi' => 'float',
        ];
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    public function impor(): BelongsTo
    {
        return $this->belongsTo(Impor::class, 'impor_id');
    }
}

