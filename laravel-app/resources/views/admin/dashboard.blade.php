@extends('layouts.admin')

@section('title', 'Dashboard Administrator')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Dashboard Ringkasan Operasional</h1>
            <p class="text-xs text-slate-400 mt-1">Status kelengkapan data harian, integrasi layanan prediksi, dan sistem audit</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.data.index') }}" class="px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition">
                Kelola Data Harian &rarr;
            </a>
        </div>
    </div>

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Tanggal Terdaftar -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-xs font-medium text-slate-400">Total Tanggal Data</span>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-black text-white">{{ $totalDates }}</span>
                <span class="text-[11px] text-emerald-400 font-semibold">{{ $completeDates }} Lengkap (31/31)</span>
            </div>
            <div class="w-full bg-slate-950 rounded-full h-1.5 mt-2">
                <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $totalDates > 0 ? ($completeDates / $totalDates * 100) : 0 }}%"></div>
            </div>
        </div>

        <!-- Status Prediksi Berhasil -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-xs font-medium text-slate-400">Prediksi Berhasil</span>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-black text-emerald-400">{{ $statusCounts['berhasil'] ?? 0 }}</span>
                <span class="text-[11px] text-slate-400">dari {{ $totalDates }} tanggal</span>
            </div>
            <span class="text-[10px] text-slate-500 block">Status valid dan siap tampil di publik</span>
        </div>

        <!-- Versi Model Aktif -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-xs font-medium text-slate-400">Model Aktif</span>
            <div class="flex items-center space-x-2">
                <span class="text-xl font-bold font-mono text-white">{{ $modelVersi }}</span>
                @if($isSimulasi)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">SIMULASI</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">PROD ML</span>
                @endif
            </div>
            <span class="text-[10px] text-slate-500 block">Dikonfigurasi di .env</span>
        </div>

        <!-- Layanan ML FastAPI Status -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-xs font-medium text-slate-400">Layanan Prediksi Python</span>
            <div class="flex items-center space-x-2 mt-1">
                @if($mlStatus['online'])
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm font-bold text-emerald-400">127.0.0.1:8001 Aktif</span>
                @else
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <span class="text-sm font-bold text-rose-400">Offline / Mati</span>
                @endif
            </div>
            <span class="text-[10px] text-slate-500 block truncate">{{ $mlStatus['message'] }}</span>
        </div>
    </div>

    <!-- Integrasi & Validasi Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Status Validasi GeoJSON -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Pemeriksaan Integritas GeoJSON</span>
                </h3>
                @if($geojsonStatus['valid'])
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">LOLOS (31/31)</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">GAGAL</span>
                @endif
            </div>
            <p class="text-xs text-slate-400">
                Berkas <code>public/data/kelurahan.geojson</code> diverifikasi memiliki 31 fitur poligon dan setiap poligon memuat <code>properties.kode_wilayah</code> yang cocok 100% dengan database Kemendagri.
            </p>
            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800 text-xs text-slate-300 flex items-center justify-between">
                <span>Status Pemeriksaan:</span>
                <span class="font-medium {{ $geojsonStatus['valid'] ? 'text-emerald-400' : 'text-rose-400' }}">{{ $geojsonStatus['message'] }}</span>
            </div>
        </div>

        <!-- Rincian Status Prediksi -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Distribusi Status Prediksi</span>
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800">
                    <span class="text-slate-400 block text-[10px]">Berhasil</span>
                    <span class="text-lg font-bold text-emerald-400">{{ $statusCounts['berhasil'] ?? 0 }}</span>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800">
                    <span class="text-slate-400 block text-[10px]">Kedaluwarsa</span>
                    <span class="text-lg font-bold text-amber-400">{{ $statusCounts['kedaluwarsa'] ?? 0 }}</span>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800">
                    <span class="text-slate-400 block text-[10px]">Belum Dihitung</span>
                    <span class="text-lg font-bold text-slate-300">{{ $statusCounts['belum_dihitung'] ?? 0 }}</span>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800">
                    <span class="text-slate-400 block text-[10px]">Gagal</span>
                    <span class="text-lg font-bold text-rose-400">{{ $statusCounts['gagal'] ?? 0 }}</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-500">
                Hasil prediksi lama tetap dipertahankan jika terjadi kegagalan atau status kedaluwarsa.
            </p>
        </div>

    </div>

    <!-- Riwayat Impor & Aktivitas Terakhir -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Riwayat Impor Terakhir -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Riwayat Impor Terakhir</h3>
                <a href="{{ route('admin.impor.index') }}" class="text-xs text-sky-400 hover:text-sky-300">Lihat Semua &rarr;</a>
            </div>
            <div class="space-y-2">
                @forelse($latestImpor as $imp)
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-200 block">{{ $imp->nama_berkas }}</span>
                            <span class="text-[10px] text-slate-400">{{ $imp->dimulai_pada?->format('d M Y H:i') }} WIB &bull; Sumber: {{ $imp->sumber ?? '-' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $imp->status === 'berhasil' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {{ strtoupper($imp->status) }}
                            </span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">{{ number_format($imp->jumlah_berhasil) }} baris</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">Belum ada riwayat impor CSV.</p>
                @endforelse
            </div>
        </div>

        <!-- Log Aktivitas Terakhir -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Log Aktivitas Admin</h3>
                <a href="{{ route('admin.aktivitas.index') }}" class="text-xs text-sky-400 hover:text-sky-300">Lihat Semua &rarr;</a>
            </div>
            <div class="space-y-2">
                @forelse($latestLogs as $log)
                    <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $log->aksi === 'delete' ? 'bg-rose-950 text-rose-300' : ($log->aksi === 'update' ? 'bg-amber-950 text-amber-300' : 'bg-blue-950 text-blue-300') }}">
                                {{ $log->aksi }}
                            </span>
                            <span class="text-slate-300 truncate max-w-[200px]">{{ $log->tabel ? 'Tabel: ' . $log->tabel : '-' }}</span>
                        </div>
                        <span class="text-[10px] text-slate-500 shrink-0">{{ $log->waktu?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">Belum ada aktivitas tercatat.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

