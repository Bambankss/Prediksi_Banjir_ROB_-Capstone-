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
        Schema::create('prediksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelurahan_id')->constrained('kelurahan')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('model_versi');
            $table->float('probabilitas'); // 0-1
            $table->string('tingkat'); // rendah|sedang|tinggi
            $table->json('fitur_json'); // snapshot fitur yang dipakai
            $table->boolean('is_simulasi')->default(false);
            $table->timestamps();
            $table->unique(['kelurahan_id', 'tanggal', 'model_versi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediksi');
    }
};

