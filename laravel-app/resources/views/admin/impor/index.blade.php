@extends('layouts.admin')

@section('title', 'Riwayat Impor Data — Admin Portal')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Riwayat & Laporan Impor CSV</h1>
            <p class="text-xs text-slate-400 mt-1">Audit log setiap batch data harian yang diimpor ke sistem</p>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">ID</th>
                        <th class="px-4 py-3.5">Nama Berkas</th>
                        <th class="px-4 py-3.5">Sumber</th>
                        <th class="px-4 py-3.5">Total Baris</th>
                        <th class="px-4 py-3.5">Berhasil</th>
                        <th class="px-4 py-3.5">Galat</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Waktu Eksekusi</th>
                        <th class="px-4 py-3.5 text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($imporList as $imp)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3.5 font-mono text-slate-400">#{{ $imp->id }}</td>
                            <td class="px-4 py-3.5 font-bold text-white">{{ $imp->nama_berkas }}</td>
                            <td class="px-4 py-3.5 text-slate-300">{{ $imp->sumber ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-mono">{{ number_format($imp->jumlah_baris) }}</td>
                            <td class="px-4 py-3.5 font-mono text-emerald-400">{{ number_format($imp->jumlah_berhasil) }}</td>
                            <td class="px-4 py-3.5 font-mono {{ $imp->jumlah_galat > 0 ? 'text-rose-400 font-bold' : 'text-slate-500' }}">
                                {{ number_format($imp->jumlah_galat) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $imp->status === 'berhasil' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                                    {{ $imp->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-400">
                                {{ $imp->dimulai_pada?->format('d/m/Y H:i:s') }} WIB
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('admin.impor.show', $imp->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-400 font-semibold border border-slate-700 text-xs">
                                    Lihat Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                Belum ada riwayat impor data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($imporList->hasPages())
            <div class="px-4 py-3 border-t border-slate-800 bg-slate-950">
                {{ $imporList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

