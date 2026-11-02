@extends('layouts.admin')

@section('title', 'Log Aktivitas Administrator — Admin Portal')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Log Aktivitas Administrator</h1>
            <p class="text-xs text-slate-400 mt-1">Rekam jejak audit keamanan dan manipulasi data operasional sistem</p>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">Waktu (WIB)</th>
                        <th class="px-4 py-3.5">Aksi</th>
                        <th class="px-4 py-3.5">Tabel Terkait</th>
                        <th class="px-4 py-3.5">Rincian Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3.5 font-mono text-slate-400 whitespace-nowrap">
                                {{ $log->waktu?->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $log->aksi === 'delete' ? 'bg-rose-950 text-rose-300 border border-rose-700/40' : ($log->aksi === 'update' ? 'bg-amber-950 text-amber-300 border border-amber-700/40' : ($log->aksi === 'hitung' ? 'bg-sky-950 text-sky-300 border border-sky-700/40' : 'bg-slate-800 text-slate-300 border border-slate-700/40')) }}">
                                    {{ $log->aksi }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-300">
                                {{ $log->tabel ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[11px] text-slate-400 max-w-md truncate" title="{{ json_encode($log->detail) }}">
                                {{ json_encode($log->detail) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                Belum ada riwayat aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-800 bg-slate-950">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

