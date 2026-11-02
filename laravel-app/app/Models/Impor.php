<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Impor extends Model
{
    use HasFactory;

    protected $table = 'impor';

    protected $fillable = [
        'nama_berkas',
        'jenis',
        'sumber',
        'admin_id',
        'status',
        'jumlah_baris',
        'jumlah_berhasil',
        'jumlah_galat',
        'ringkasan_galat',
        'dimulai_pada',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'ringkasan_galat' => 'array',
            'jumlah_baris' => 'integer',
            'jumlah_berhasil' => 'integer',
            'jumlah_galat' => 'integer',
            'dimulai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
        ];
    }

    public function dataHarian(): HasMany
    {
        return $this->hasMany(DataHarian::class, 'impor_id');
    }
}

