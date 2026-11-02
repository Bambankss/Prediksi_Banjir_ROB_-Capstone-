@extends('layouts.admin')

@section('title', "Detail Impor #{$impor->id}")

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.impor.index') }}" class="hover:text-white">&larr; Kembali ke Riwayat Impor</a>
                <span>&bull;</span>
                <span>Audit Batch</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Detail Impor: <span class="font-mono text-sky-400">{{ $impor->nama_berkas }}</span></h1>
            <p class="text-xs text-slate-400 mt-1">ID Batch #{{ $impor->id }} &bull; Sumber: {{ $impor->sumber ?? '-' }}</p>
        </div>

        <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase {{ $impor->status === 'berhasil' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
            {{ $impor->status }}
        </span>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-slate-400 block text-[11px]">Total Baris</span>
            <span class="text-xl font-bold text-white mt-1 block font-mono">{{ number_format($impor->jumlah_baris) }}</span>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-slate-400 block text-[11px]">Baris Berhasil</span>
            <span class="text-xl font-bold text-emerald-400 mt-1 block font-mono">{{ number_format($impor->jumlah_berhasil) }}</span>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-slate-400 block text-[11px]">Baris Galat</span>
            <span class="text-xl font-bold text-rose-400 mt-1 block font-mono">{{ number_format($impor->jumlah_galat) }}</span>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-slate-400 block text-[11px]">Waktu Selesai</span>
            <span class="text-xs font-medium text-slate-200 mt-1 block">{{ $impor->selesai_pada?->format('d M Y H:i:s') ?? '-' }} WIB</span>
        </div>
    </div>

    <!-- Daftar Galat (Jika Ada) -->
    @if(!empty($impor->ringkasan_galat))
    <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
        <h3 class="text-xs font-bold text-rose-400 uppercase tracking-wider flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Daftar Galat Per Baris (Maks. 200 Catatan)</span>
        </h3>
        <div class="overflow-x-auto max-h-96 overflow-y-auto rounded-lg border border-slate-800">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 sticky top-0 border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-2 w-24">Baris</th>
                        <th class="px-4 py-2">Pesan Galat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($impor->ringkasan_galat as $err)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-4 py-2 font-mono text-slate-400">Baris {{ $err['baris'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-rose-300">{{ $err['pesan'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-400">
        <svg class="w-8 h-8 text-emerald-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <p class="font-medium text-slate-200">Tidak ada galat pada proses impor ini.</p>
        <p class="text-[11px] text-slate-500 mt-0.5">Seluruh baris valid dan berhasil disimpan ke database.</p>
    </div>
    @endif

</div>
@endsection

