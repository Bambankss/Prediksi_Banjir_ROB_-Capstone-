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
        Schema::create('kelurahan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_wilayah')->unique(); // teks NN.NN.NN.NNNN
            $table->string('nama');
            $table->string('kecamatan');
            $table->float('elevasi'); // m
            $table->float('jarak_pantai'); // km
            $table->string('stasiun_pasut'); // 'Sunda Kelapa' atau 'Kolinlamil'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelurahan');
    }
};

