<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('impor', function (Blueprint $table) {
            $table->id();
            $table->string('nama_berkas');
            $table->string('jenis'); // mis. 'data_harian'
            $table->string('sumber')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('status'); // 'berhasil' | 'gagal'
            $table->integer('jumlah_baris')->default(0);
            $table->integer('jumlah_berhasil')->default(0);
            $table->integer('jumlah_galat')->default(0);
            $table->json('ringkasan_galat')->nullable();
            $table->timestamp('dimulai_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impor');
    }
};

