<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'admin_id',
        'aksi',
        'tabel',
        'ref_id',
        'detail',
        'waktu',
    ];

    protected function casts(): array
    {
        return [
            'detail' => 'array',
            'waktu' => 'datetime',
        ];
    }
}

