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
        Schema::create('status_prediksi', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('model_versi');
            $table->string('status')->default('belum_dihitung'); // belum_dihitung|menghitung|berhasil|kedaluwarsa|gagal
            $table->boolean('is_simulasi')->default(false);
            $table->integer('jumlah_hasil')->default(0);
            $table->timestamp('dihitung_pada')->nullable();
            $table->text('pesan_galat')->nullable();
            $table->timestamps();
            $table->unique(['tanggal', 'model_versi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_prediksi');
    }
};

