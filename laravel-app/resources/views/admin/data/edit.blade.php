@extends('layouts.admin')

@section('title', "Edit Data Harian — {$tanggal}")

@section('content')
<div class="space-y-6" x-data="editDataHarian()">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.data.index') }}" class="hover:text-white">&larr; Kembali ke Daftar</a>
                <span>&bull;</span>
                <span>Edit 31 Kelurahan</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Edit Data Harian: <span class="font-mono text-sky-400">{{ $tanggal }}</span></h1>
            <p class="text-xs text-slate-400 mt-1">Perubahan data akan otomatis memicu perhitungan ulang prediksi untuk tanggal ini.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.data.index') }}" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition">
                Batal
            </a>
            <button type="submit" form="form-edit-data" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold shadow-lg shadow-sky-600/20 transition">
                Simpan & Hitung Ulang
            </button>
        </div>
    </div>

    <!-- Peringatan Kelurahan yang Belum Punya Data (A5) -->
    @if($missingKelurahan->isNotEmpty())
    <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-600/40 text-rose-200 space-y-2">
        <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-300">Data Belum Lengkap! ({{ $missingKelurahan->count() }} Kelurahan Belum Memiliki Data)</h3>
        </div>
        <p class="text-xs text-rose-300/90 leading-relaxed">
            Prediksi hanya dapat dihitung jika seluruh 31 kelurahan memiliki data lengkap. Kelurahan yang masih kosong:
        </p>
        <div class="flex flex-wrap gap-1.5 pt-1">
            @foreach($missingKelurahan as $mKel)
                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-rose-900/60 text-rose-200 border border-rose-700/50">
                    {{ $mKel->nama }} ({{ $mKel->kode_wilayah }})
                </span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Alat Bantu: Isi Cepat (A7) -->
    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
        <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center space-x-1.5">
            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Alat Bantu: Isi Cepat Nilai Pasang & Gelombang</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-[10px] text-slate-400 font-medium mb-1">Pasang Sunda Kelapa (m)</label>
                <input type="number" step="0.01" x-model="quickPasangSundaKelapa" placeholder="Contoh: 1.05"
                    class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white">
            </div>
            <div>
                <label class="block text-[10px] text-slate-400 font-medium mb-1">Pasang Kolinlamil (m)</label>
                <input type="number" step="0.01" x-model="quickPasangKolinlamil" placeholder="Contoh: 1.10"
                    class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white">
            </div>
            <div>
                <label class="block text-[10px] text-slate-400 font-medium mb-1">Gelombang Semua Kelurahan (m)</label>
                <input type="number" step="0.01" x-model="quickGelombang" placeholder="Contoh: 0.50"
                    class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white">
            </div>
            <div class="flex items-end">
                <button type="button" @click="applyQuickFill()" class="w-full py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-400 font-semibold border border-slate-700 transition">
                    Terapkan Nilai
                </button>
            </div>
        </div>
    </div>

    <!-- Form Tabel 31 Kelurahan -->
    <form id="form-edit-data" method="POST" action="{{ route('admin.data.update', $tanggal) }}">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Kelurahan / Kode</th>
                            <th class="px-3 py-3">Stasiun Pasut</th>
                            <th class="px-3 py-3">Elevasi (m)</th>
                            <th class="px-3 py-3">Jarak Pantai (km)</th>
                            <th class="px-3 py-3 w-32">Curah Hujan (mm)</th>
                            <th class="px-3 py-3 w-32">Pasang Tertinggi (m)</th>
                            <th class="px-3 py-3 w-32">Gelombang Tertinggi (m)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach($kelurahans as $kel)
                            @php
                                $rec = $harianRecords[$kel->id] ?? null;
                                $hujan = old("items.{$kel->id}.curah_hujan", $rec?->curah_hujan ?? '');
                                $pasang = old("items.{$kel->id}.pasang_tertinggi", $rec?->pasang_tertinggi ?? '');
                                $gelombang = old("items.{$kel->id}.gelombang_tertinggi", $rec?->gelombang_tertinggi ?? '');
                            @endphp
                            <tr class="hover:bg-slate-800/30 transition">
                                <!-- Kelurahan -->
                                <td class="px-4 py-2.5">
                                    <span class="font-bold text-white block">{{ $kel->nama }}</span>
                                    <span class="text-[10px] text-slate-400 block font-mono">{{ $kel->kode_wilayah }} &bull; {{ $kel->kecamatan }}</span>
                                </td>

                                <!-- Stasiun Pasut -->
                                <td class="px-3 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $kel->stasiun_pasut === 'Sunda Kelapa' ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/40' : 'bg-indigo-950 text-indigo-300 border border-indigo-700/40' }}">
                                        {{ $kel->stasiun_pasut }}
                                    </span>
                                </td>

                                <!-- Elevasi -->
                                <td class="px-3 py-2.5 font-mono text-slate-400">
                                    {{ number_format($kel->elevasi, 2) }}
                                </td>

                                <!-- Jarak Pantai -->
                                <td class="px-3 py-2.5 font-mono text-slate-400">
                                    {{ number_format($kel->jarak_pantai, 3) }}
                                </td>

                                <!-- Curah Hujan Input -->
                                <td class="px-3 py-2">
                                    <input type="number" step="0.1" min="0" required
                                        name="items[{{ $kel->id }}][curah_hujan]"
                                        value="{{ $hujan }}"
                                        id="hujan-{{ $kel->id }}"
                                        class="w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Pasang Input -->
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" required
                                        name="items[{{ $kel->id }}][pasang_tertinggi]"
                                        value="{{ $pasang }}"
                                        data-stasiun="{{ $kel->stasiun_pasut }}"
                                        class="pasang-input w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Gelombang Input -->
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" required
                                        name="items[{{ $kel->id }}][gelombang_tertinggi]"
                                        value="{{ $gelombang }}"
                                        class="gelombang-input w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Action -->
            <div class="px-4 py-3 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-500">Pastikan seluruh 31 kelurahan terisi angka yang valid.</span>
                <button type="submit" class="px-5 py-2 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold shadow transition">
                    Simpan Perubahan & Hitung Ulang
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function editDataHarian() {
    return {
        quickPasangSundaKelapa: '',
        quickPasangKolinlamil: '',
        quickGelombang: '',

        applyQuickFill() {
            if (this.quickPasangSundaKelapa) {
                document.querySelectorAll('.pasang-input[data-stasiun="Sunda Kelapa"]').forEach(el => {
                    el.value = this.quickPasangSundaKelapa;
                });
            }
            if (this.quickPasangKolinlamil) {
                document.querySelectorAll('.pasang-input[data-stasiun="Kolinlamil"]').forEach(el => {
                    el.value = this.quickPasangKolinlamil;
                });
            }
            if (this.quickGelombang) {
                document.querySelectorAll('.gelombang-input').forEach(el => {
                    el.value = this.quickGelombang;
                });
            }
            alert('Nilai cepat berhasil diterapkan ke formulir.');
        }
    };
}
</script>
@endpush

