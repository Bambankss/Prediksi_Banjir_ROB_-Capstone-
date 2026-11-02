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
        Schema::create('data_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelurahan_id')->constrained('kelurahan')->onDelete('cascade');
            $table->date('tanggal');
            $table->float('curah_hujan'); // mm/hari, >= 0
            $table->float('pasang_tertinggi'); // m
            $table->float('gelombang_tertinggi'); // m, >= 0
            $table->string('sumber')->default('manual'); // 'manual' | 'impor_csv' | 'seed'
            $table->foreignId('impor_id')->nullable()->constrained('impor')->nullOnDelete();
            $table->timestamps();
            $table->unique(['kelurahan_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_harian');
    }
};

