@extends('layouts.admin')

@section('title', 'Kelola Data Harian — Admin Portal')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftar Tanggal & Status Prediksi</h1>
            <p class="text-xs text-slate-400 mt-1">Pantau kelengkapan 31 kelurahan dan hitung ulang prediksi per tanggal</p>
        </div>
        
        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.data.index') }}" class="flex items-center space-x-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari tanggal (YYYY-MM-DD)..."
                class="bg-slate-900 border border-slate-700 text-xs text-slate-100 rounded-lg px-3 py-2 w-56 focus:outline-none focus:ring-2 focus:ring-sky-500">
            <button type="submit" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">
                Cari
            </button>
            @if($search)
                <a href="{{ route('admin.data.index') }}" class="px-2 py-2 text-xs text-slate-400 hover:text-white">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table Section -->
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Kelengkapan Data</th>
                        <th class="px-4 py-3.5">Status Prediksi</th>
                        <th class="px-4 py-3.5">Model / Simulasi</th>
                        <th class="px-4 py-3.5">Terakhir Dihitung</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($dates as $item)
                        @php
                            $tgl = \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
                            $status = $statusList[$tgl] ?? null;
                            $statusNama = $status ? $status->status : 'belum_dihitung';
                            $isComplete = ($item->total_kelurahan == 31);
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <!-- Tanggal -->
                            <td class="px-4 py-3.5 font-medium text-white">
                                <span class="font-mono text-sm block">{{ $tgl }}</span>
                                <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($tgl)->translatedFormat('l, d F Y') }}</span>
                            </td>

                            <!-- Kelengkapan -->
                            <td class="px-4 py-3.5">
                                @if($isComplete)
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-950/80 text-emerald-300 border border-emerald-600/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span>31 / 31 (Lengkap)</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-950/80 text-rose-300 border border-rose-600/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        <span>{{ $item->total_kelurahan }} / 31 (Kurang)</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Status Prediksi -->
                            <td class="px-4 py-3.5">
                                @if($statusNama === 'berhasil')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 uppercase">
                                        Berhasil
                                    </span>
                                @elseif($statusNama === 'kedaluwarsa')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 uppercase">
                                        Kedaluwarsa
                                    </span>
                                @elseif($statusNama === 'menghitung')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/40 uppercase animate-pulse">
                                        Menghitung...
                                    </span>
                                @elseif($statusNama === 'gagal')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 uppercase">
                                        Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-800 text-slate-400 uppercase">
                                        Belum Dihitung
                                    </span>
                                @endif

                                @if($status && $status->pesan_galat)
                                    <span class="block text-[10px] text-rose-400 max-w-xs truncate mt-0.5" title="{{ $status->pesan_galat }}">
                                        {{ $status->pesan_galat }}
                                    </span>
                                @endif
                            </td>

                            <!-- Model / Simulasi -->
                            <td class="px-4 py-3.5">
                                <span class="font-mono text-xs block text-slate-300">{{ $status?->model_versi ?? $modelVersi }}</span>
                                @if($status?->is_simulasi || str_starts_with($modelVersi, 'stub'))
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        SIMULASI
                                    </span>
                                @endif
                            </td>

                            <!-- Waktu Dihitung -->
                            <td class="px-4 py-3.5 text-slate-400 text-xs">
                                @if($status && $status->dihitung_pada)
                                    <span>{{ \Carbon\Carbon::parse($status->dihitung_pada)->format('d/m/Y H:i') }} WIB</span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <!-- Hitung Ulang Button -->
                                <form method="POST" action="{{ route('admin.data.hitung', $tgl) }}" class="inline">
                                    @csrf
                                    <button type="submit" {{ !$isComplete ? 'disabled' : '' }}
                                        title="{{ !$isComplete ? 'Data harus lengkap 31/31 sebelum dihitung' : 'Hitung ulang prediksi' }}"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-sky-950 hover:bg-sky-900 text-sky-300 border border-sky-600/40 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                        Hitung Ulang
                                    </button>
                                </form>

                                <!-- Edit Data -->
                                <a href="{{ route('admin.data.edit', $tgl) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                                    Edit
                                </a>

                                <!-- Delete Data -->
                                <form method="POST" action="{{ route('admin.data.destroy', $tgl) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SELURUH data harian dan prediksi untuk tanggal {{ $tgl }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1.5 rounded-lg text-xs font-semibold text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Tidak ada data tanggal yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($dates->hasPages())
            <div class="px-4 py-3 border-t border-slate-800 bg-slate-950">
                {{ $dates->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

