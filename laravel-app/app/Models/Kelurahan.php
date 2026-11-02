<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelurahan extends Model
{
    use HasFactory;

    protected $table = 'kelurahan';

    protected $fillable = [
        'kode_wilayah',
        'nama',
        'kecamatan',
        'elevasi',
        'jarak_pantai',
        'stasiun_pasut',
    ];

    protected function casts(): array
    {
        return [
            'elevasi' => 'float',
            'jarak_pantai' => 'float',
        ];
    }

    public function dataHarian(): HasMany
    {
        return $this->hasMany(DataHarian::class, 'kelurahan_id');
    }

    public function prediksi(): HasMany
    {
        return $this->hasMany(Prediksi::class, 'kelurahan_id');
    }
}

