@extends('layouts.app')

@section('title', 'Peta Prediksi Banjir ROB Jakarta Utara')

@push('styles')
<style>
/* Hilangkan Watermark dan Teks Attribution Leaflet Secara Total */
.leaflet-control-attribution,
.leaflet-control-attribution *,
.leaflet-attribution-flag,
.leaflet-bottom.leaflet-right {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
    pointer-events: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Pastikan Map Mengisi Penuh 100% Kontainer Tanpa Ruang Kosong */
#map {
    width: 100% !important;
    height: 100% !important;
    min-height: 100% !important;
    background: #020617 !important;
}

.leaflet-dark-tooltip {
    background: #0f172a !important;
    border: 1px solid #334155 !important;
    color: #f8fafc !important;
    border-radius: 8px !important;
    padding: 6px 10px !important;
    font-size: 11px !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4) !important;
}
.leaflet-dark-tooltip::before {
    border-top-color: #0f172a !important;
}

/* Kustomisasi Kalender Flatpickr Dark Theme */
.flatpickr-calendar {
    background: #0f172a !important;
    border: 1px solid #334155 !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6) !important;
    border-radius: 12px !important;
    padding: 4px !important;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
}
.flatpickr-day:hover {
    background: #1e293b !important;
}
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
    color: #334155 !important;
    opacity: 0.3 !important;
    cursor: not-allowed !important;
}
</style>
@endpush

@section('content')
<div x-data="petaDashboard()" class="flex-1 flex flex-col h-full w-full overflow-hidden min-h-0">

    <!-- Banner Simulasi (Bila Versi Stub) -->
    @if($isSimulasi)
    <div class="bg-amber-950/90 border-b border-amber-600/40 px-4 py-2 text-xs text-amber-200 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">SIMULASI</span>
            <span><strong>Hasil Simulasi ({{ $modelVersi }})</strong> — Data prediksi dihasilkan oleh layanan tiruan (stub) untuk keperluan demonstrasi sistem.</span>
        </div>
        <a href="{{ route('tentang') }}" class="underline hover:text-white shrink-0 ml-2">Pelajari Selengkapnya</a>
    </div>
    @endif

    <!-- Control & Filter Bar -->
    <div class="bg-slate-900 border-b border-slate-800 px-4 py-2.5 shrink-0 z-30">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
            
            <!-- Date Picker & Cari (Input Date dengan Tombol Cari Manual) -->
            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <label for="date-input" class="text-xs font-semibold text-slate-300 shrink-0">Tanggal:</label>
                
                <div class="relative flex-1 sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-sky-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="text" id="date-input" x-ref="dateInput" placeholder="Pilih tanggal prediksi..." readonly
                        class="w-full bg-slate-950 border border-slate-700 text-slate-100 text-sm font-medium rounded-lg pl-9 pr-8 py-1.5 focus:ring-2 focus:ring-sky-500 focus:outline-none cursor-pointer">
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Tombol Navigasi Cepat (Sebelumnya / Berikutnya - Otomatis Perbarui) -->
                <div class="flex items-center bg-slate-950 border border-slate-700 rounded-lg overflow-hidden shrink-0">
                    <button type="button" @click="prevDate()" :disabled="!hasPrevDate()" title="Hari Sebelumnya"
                        class="px-2 py-1.5 text-slate-400 hover:text-white hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="w-px h-4 bg-slate-800"></div>
                    <button type="button" @click="nextDate()" :disabled="!hasNextDate()" title="Hari Berikutnya"
                        class="px-2 py-1.5 text-slate-400 hover:text-white hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <!-- Tombol Cari Manual untuk Eksekusi Pilihan Input Date -->
                <button @click="loadData()" :disabled="loading || !selectedDate"
                    class="px-4 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 active:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold transition shadow-md shadow-sky-600/25 flex items-center space-x-1.5 shrink-0">
                    <template x-if="loading">
                        <svg class="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!loading">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Cari</span>
                </button>
            </div>

            <!-- Ringkasan Statistik -->
            <div class="flex items-center space-x-2 text-xs">
                <div class="flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-emerald-950/60 border border-emerald-600/30 text-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="font-medium">Rendah:</span>
                    <span class="font-bold text-white" x-text="stats.rendah">0</span>
                </div>
                <div class="flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-amber-950/60 border border-amber-600/30 text-amber-300">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="font-medium">Sedang:</span>
                    <span class="font-bold text-white" x-text="stats.sedang">0</span>
                </div>
                <div class="flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-rose-950/60 border border-rose-600/30 text-rose-300">
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    <span class="font-medium">Tinggi:</span>
                    <span class="font-bold text-white" x-text="stats.tinggi">0</span>
                </div>
            </div>

            <!-- Filter Kelurahan & Informasi Model -->
            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-44">
                    <input type="text" x-model="searchQuery" placeholder="Cari kelurahan..." class="w-full bg-slate-950 border border-slate-700 text-slate-100 text-xs rounded-lg pl-8 pr-3 py-1.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select x-model="filterTingkat" class="bg-slate-950 border border-slate-700 text-slate-100 text-xs rounded-lg px-2 py-1.5 focus:outline-none">
                    <option value="semua">Semua</option>
                    <option value="tinggi">Tinggi</option>
                    <option value="sedang">Sedang</option>
                    <option value="rendah">Rendah</option>
                </select>

                <!-- Model Aktif Badge di Top Bar (Bersih, Bukan di Area Peta) -->
                <div class="hidden xl:flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                    <span>Model: <strong class="text-slate-200" x-text="meta.model_versi || '{{ $modelVersi }}'"></strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace (Map + Detail Panel - Mengisi 100% Sisa Layar) -->
    <div class="flex-1 relative flex flex-col md:flex-row overflow-hidden min-h-0 h-full w-full">

        <!-- Peta Leaflet Container (100% Full Height & Width) -->
        <div class="flex-1 relative bg-slate-950 h-full w-full min-h-0 overflow-hidden">
            <div id="map" class="w-full h-full z-10"></div>

            <!-- State: Loading Overlay -->
            <div x-show="loading" class="absolute inset-0 bg-slate-950/70 z-20 flex flex-col items-center justify-center backdrop-blur-xs">
                <div class="w-12 h-12 border-4 border-sky-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="mt-3 text-sm text-sky-300 font-medium tracking-wide">Memuat data prediksi banjir ROB...</span>
            </div>

            <!-- State: Error Overlay -->
            <div x-show="errorMessage" class="absolute inset-0 bg-slate-950/80 z-20 flex flex-col items-center justify-center p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="text-slate-200 text-sm max-w-md font-medium" x-text="errorMessage"></p>
                <button @click="loadData()" class="mt-4 px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700">Coba Lagi</button>
            </div>

            <!-- Legenda Peta (Float Bottom-Left) -->
            <div class="absolute bottom-5 left-5 z-20 bg-slate-900/90 backdrop-blur border border-slate-800 rounded-xl p-3 shadow-2xl text-xs space-y-2 pointer-events-auto">
                <span class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider">Tingkat Risiko ROB</span>
                <div class="space-y-1.5">
                    <div class="flex items-center space-x-2">
                        <span class="w-3.5 h-3.5 rounded bg-rose-500 border border-rose-600"></span>
                        <span class="text-slate-200 font-medium">Tinggi (P &ge; 65%)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3.5 h-3.5 rounded bg-amber-500 border border-amber-600"></span>
                        <span class="text-slate-200 font-medium">Sedang (35% - 64%)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3.5 h-3.5 rounded bg-emerald-500 border border-emerald-600"></span>
                        <span class="text-slate-200 font-medium">Rendah (&lt; 35%)</span>
                    </div>
                </div>
                <div class="border-t border-slate-800 pt-1 text-[10px] text-slate-400">
                    Klik kelurahan untuk melihat detail fitur
                </div>
            </div>
        </div>

        <!-- Detail Panel (Slide-in / Sidebar Kanan) -->
        <div class="w-full md:w-96 bg-slate-900 border-l border-slate-800 flex flex-col shrink-0 z-20 overflow-y-auto h-full" :class="selectedKelurahan ? 'block' : 'hidden md:block'">
            
            <template x-if="selectedKelurahan">
                <div class="p-5 flex-1 flex flex-col space-y-5">
                    <!-- Header Kelurahan -->
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs text-sky-400 font-medium uppercase tracking-wider" x-text="selectedKelurahan.kecamatan"></span>
                            <h2 class="text-xl font-bold text-white tracking-tight" x-text="selectedKelurahan.nama"></h2>
                            <span class="text-xs text-slate-400 block font-mono" x-text="selectedKelurahan.kode_wilayah"></span>
                        </div>
                        <button @click="selectedKelurahan = null; resetHighlight();" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Kartu Tingkat Risiko & Probabilitas -->
                    <div class="p-4 rounded-xl border" :class="{
                        'bg-rose-950/40 border-rose-600/40 text-rose-200': selectedKelurahan.tingkat === 'tinggi',
                        'bg-amber-950/40 border-amber-600/40 text-amber-200': selectedKelurahan.tingkat === 'sedang',
                        'bg-emerald-950/40 border-emerald-600/40 text-emerald-200': selectedKelurahan.tingkat === 'rendah'
                    }">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider">Tingkat Risiko</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full uppercase" :class="{
                                'bg-rose-500/20 text-rose-300 border border-rose-500/40': selectedKelurahan.tingkat === 'tinggi',
                                'bg-amber-500/20 text-amber-300 border border-amber-500/40': selectedKelurahan.tingkat === 'sedang',
                                'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': selectedKelurahan.tingkat === 'rendah'
                            }" x-text="selectedKelurahan.tingkat"></span>
                        </div>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span class="text-2xl font-black text-white" x-text="(selectedKelurahan.probabilitas * 100).toFixed(1) + '%'"></span>
                            <span class="text-xs text-slate-400">Peluang Banjir ROB</span>
                        </div>
                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-900 rounded-full h-2 mt-2.5 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-500" :style="'width: ' + (selectedKelurahan.probabilitas * 100) + '%;'" :class="{
                                'bg-rose-500': selectedKelurahan.tingkat === 'tinggi',
                                'bg-amber-500': selectedKelurahan.tingkat === 'sedang',
                                'bg-emerald-500': selectedKelurahan.tingkat === 'rendah'
                            }"></div>
                        </div>
                    </div>

                    <!-- Parameter Fitur (5 Fitur Baku) -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-semibold text-slate-300 uppercase tracking-wider flex items-center justify-between">
                            <span>Parameter Masukan Model</span>
                            <span class="text-[10px] text-slate-500 font-normal">Kontrak 5 Fitur</span>
                        </h3>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <!-- Elevasi -->
                            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                                <span class="text-slate-400 block text-[11px]">Elevasi Tanah</span>
                                <span class="text-base font-bold text-white mt-0.5 block" x-text="selectedKelurahan.fitur.elevasi + ' m'"></span>
                                <span class="text-[10px] text-slate-500">Statis</span>
                            </div>

                            <!-- Jarak Pantai -->
                            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                                <span class="text-slate-400 block text-[11px]">Jarak Pantai</span>
                                <span class="text-base font-bold text-white mt-0.5 block" x-text="selectedKelurahan.fitur.jarak_pantai + ' km'"></span>
                                <span class="text-[10px] text-slate-500">Statis</span>
                            </div>

                            <!-- Curah Hujan -->
                            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                                <span class="text-slate-400 block text-[11px]">Curah Hujan</span>
                                <span class="text-base font-bold text-sky-400 mt-0.5 block" x-text="selectedKelurahan.fitur.curah_hujan + ' mm/hr'"></span>
                                <span class="text-[10px] text-slate-500">Harian</span>
                            </div>

                            <!-- Pasang Tertinggi -->
                            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                                <span class="text-slate-400 block text-[11px]">Pasang Maks</span>
                                <span class="text-base font-bold text-cyan-400 mt-0.5 block" x-text="selectedKelurahan.fitur.pasang_tertinggi + ' m'"></span>
                                <span class="text-[10px] text-slate-500" x-text="selectedKelurahan.stasiun_pasut || 'Pasut'"></span>
                            </div>

                            <!-- Gelombang Tertinggi -->
                            <div class="col-span-2 p-3 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Tinggi Gelombang Maksimum</span>
                                    <span class="text-[10px] text-slate-500">Kondisi perairan pesisir</span>
                                </div>
                                <span class="text-base font-bold text-blue-400" x-text="selectedKelurahan.fitur.gelombang_tertinggi + ' m'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan Konteks -->
                    <div class="mt-auto pt-4 border-t border-slate-800/80 text-[11px] text-slate-500 space-y-1">
                        <p>Snapshot parameter ini adalah data yang digunakan model saat menghitung risiko tanggal terpilih.</p>
                    </div>
                </div>
            </template>

            <!-- State: Empty Selection -->
            <template x-if="!selectedKelurahan">
                <div class="p-8 flex-1 flex flex-col items-center justify-center text-center text-slate-500 space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-slate-800/60 border border-slate-700/50 flex items-center justify-center text-slate-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-slate-300 font-medium text-sm">Pilih Kelurahan</h4>
                        <p class="text-xs text-slate-500 max-w-[220px] mt-1">Klik salah satu wilayah pada peta untuk melihat data dan parameter risiko banjir ROB.</p>
                    </div>
                </div>
            </template>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function petaDashboard() {
    return {
        selectedDate: '{{ $selectedDate }}',
        availableDates: @json($availableDates),
        fp: null,
        loading: false,
        errorMessage: null,
        predictions: {},
        meta: {},
        stats: { rendah: 0, sedang: 0, tinggi: 0 },
        searchQuery: '',
        filterTingkat: 'semua',
        selectedKelurahan: null,
        map: null,
        geojsonLayer: null,
        geojsonData: null,
        selectedLayer: null,
        boundsFitted: false,

        init() {
            this.$nextTick(async () => {
                this.initDatePicker();
                this.initMap();
                await this.loadGeoJson();
                if (this.selectedDate) {
                    await this.loadData();
                }
            });
        },

        initDatePicker() {
            if (typeof flatpickr !== 'undefined' && this.$refs.dateInput) {
                const self = this;
                this.fp = flatpickr(this.$refs.dateInput, {
                    locale: typeof flatpickr.l10ns.id !== 'undefined' ? flatpickr.l10ns.id : 'default',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd F Y',
                    altInputClass: 'w-full bg-slate-950 border border-slate-700 text-slate-100 text-sm font-medium rounded-lg pl-9 pr-8 py-1.5 focus:ring-2 focus:ring-sky-500 focus:outline-none cursor-pointer',
                    defaultDate: this.selectedDate,
                    enable: this.availableDates, // HANYA tanggal ini yang bisa diklik / dipilih
                    minDate: this.availableDates.length ? this.availableDates[this.availableDates.length - 1] : undefined,
                    maxDate: this.availableDates.length ? this.availableDates[0] : undefined,
                    onChange: (selectedDates, dateStr) => {
                        if (dateStr) {
                            self.selectedDate = dateStr;
                            // Catatan: Tidak memanggil self.loadData() otomatis.
                            // Pengguna menekan tombol 'Cari' untuk memuat peta sesuai permintaan.
                        }
                    }
                });
            }
        },

        hasPrevDate() {
            if (!this.availableDates || !this.availableDates.length) return false;
            const idx = this.availableDates.indexOf(this.selectedDate);
            return idx >= 0 && idx < this.availableDates.length - 1;
        },

        hasNextDate() {
            if (!this.availableDates || !this.availableDates.length) return false;
            const idx = this.availableDates.indexOf(this.selectedDate);
            return idx > 0;
        },

        prevDate() {
            const idx = this.availableDates.indexOf(this.selectedDate);
            if (idx >= 0 && idx < this.availableDates.length - 1) {
                this.setDate(this.availableDates[idx + 1], true);
            }
        },

        nextDate() {
            const idx = this.availableDates.indexOf(this.selectedDate);
            if (idx > 0) {
                this.setDate(this.availableDates[idx - 1], true);
            }
        },

        setDate(dateStr, autoLoad = false) {
            this.selectedDate = dateStr;
            if (this.fp) {
                this.fp.setDate(dateStr, false);
            }
            if (autoLoad) {
                this.loadData();
            }
        },

        initMap() {
            if (this.map) {
                this.map.invalidateSize();
                return;
            }

            const mapEl = document.getElementById('map');
            if (!mapEl) return;

            // Inisialisasi peta Leaflet TANPA watermark attribution
            this.map = L.map(mapEl, {
                center: [-6.138, 106.87],
                zoom: 12,
                zoomControl: false,
                attributionControl: false // Hilangkan attribution control bawaan
            });

            // Pastikan jika ada attributionControl terbuat, langsung dibuang
            if (this.map.attributionControl) {
                this.map.attributionControl.remove();
            }

            L.control.zoom({ position: 'topright' }).addTo(this.map);

            // Base map: CartoDB Dark Matter dengan attribution false
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 18,
                subdomains: 'abcd',
                attribution: false // Hapus teks watermark tile
            }).addTo(this.map);

            // Bersihkan elemen attribution atau flag dari DOM
            setTimeout(() => {
                const attributions = document.querySelectorAll('.leaflet-control-attribution, .leaflet-bottom.leaflet-right');
                attributions.forEach(el => el.remove());
                if (this.map) {
                    this.map.invalidateSize();
                }
            }, 100);

            // Resizing listener
            window.addEventListener('resize', () => {
                if (this.map) {
                    this.map.invalidateSize();
                }
            });
        },

        async loadGeoJson() {
            try {
                const res = await fetch('{{ asset('data/kelurahan.geojson') }}');
                if (!res.ok) throw new Error("Gagal mengambil berkas GeoJSON (" + res.status + ")");
                this.geojsonData = await res.json();
                this.renderLayers();
            } catch (err) {
                console.error("GeoJSON error:", err);
                this.errorMessage = "Gagal memuat batas wilayah peta Jakarta Utara: " + err.message;
            }
        },

        async loadData() {
            if (!this.selectedDate) return;
            this.loading = true;
            this.errorMessage = null;

            // Pastikan elemen watermark attribution terhapus dari DOM
            document.querySelectorAll('.leaflet-control-attribution, .leaflet-bottom.leaflet-right').forEach(el => el.remove());

            try {
                const url = '{{ route('data.prediksi') }}?tanggal=' + encodeURIComponent(this.selectedDate);
                const res = await fetch(url);
                const json = await res.json();

                if (json.status !== 'ok') {
                    throw new Error(json.pesan || "Data tanggal tidak tersedia");
                }

                // Map data by kode_wilayah
                const predMap = {};
                let r = 0, s = 0, t = 0;

                json.data.forEach(item => {
                    predMap[item.kode_wilayah] = item;
                    if (item.tingkat === 'tinggi') t++;
                    else if (item.tingkat === 'sedang') s++;
                    else if (item.tingkat === 'rendah') r++;
                });

                this.predictions = predMap;
                this.meta = json.meta || {};
                this.stats = { rendah: r, sedang: s, tinggi: t };

                this.renderLayers();

                // Perbarui detail kelurahan jika sedang terbuka
                if (this.selectedKelurahan) {
                    const updated = this.predictions[this.selectedKelurahan.kode_wilayah];
                    if (updated) {
                        this.selectedKelurahan = updated;
                    }
                }
            } catch (err) {
                this.errorMessage = err.message || "Gagal menghubungi server.";
                this.predictions = {};
                this.stats = { rendah: 0, sedang: 0, tinggi: 0 };
                this.renderLayers();
            } finally {
                this.loading = false;
            }
        },

        getColor(tingkat) {
            switch (tingkat) {
                case 'tinggi': return '#ef4444'; // Red
                case 'sedang': return '#f59e0b'; // Amber
                case 'rendah': return '#10b981'; // Green
                default: return '#475569';       // Slate gray (no data)
            }
        },

        renderLayers() {
            if (!this.geojsonData || !this.map) return;

            if (this.geojsonLayer) {
                this.map.removeLayer(this.geojsonLayer);
            }

            const self = this;

            this.geojsonLayer = L.geoJSON(this.geojsonData, {
                style: function (feature) {
                    const kode = feature.properties.kode_wilayah;
                    const pred = self.predictions[kode];
                    const tingkat = pred ? pred.tingkat : null;

                    // Filter search / tingkat
                    let isVisible = true;
                    if (self.searchQuery) {
                        const q = self.searchQuery.toLowerCase();
                        const nama = (pred?.nama || feature.properties.wadmkd || '').toLowerCase();
                        const kec = (pred?.kecamatan || feature.properties.kdepum || '').toLowerCase();
                        isVisible = nama.includes(q) || kec.includes(q);
                    }
                    if (self.filterTingkat !== 'semua' && tingkat !== self.filterTingkat) {
                        isVisible = false;
                    }

                    return {
                        fillColor: self.getColor(tingkat),
                        weight: isVisible ? 1.5 : 0.5,
                        opacity: isVisible ? 0.9 : 0.2,
                        color: '#1e293b',
                        fillOpacity: isVisible ? (pred ? 0.75 : 0.3) : 0.1,
                    };
                },
                onEachFeature: function (feature, layer) {
                    const kode = feature.properties.kode_wilayah;
                    const pred = self.predictions[kode];
                    const nama = pred ? pred.nama : (feature.properties.wadmkd || 'Kelurahan');
                    const tingkat = pred ? pred.tingkat.toUpperCase() : 'BELUM ADA DATA';

                    layer.bindTooltip(`<strong>${nama}</strong><br>Risiko: <span class="capitalize">${tingkat}</span>`, {
                        sticky: true,
                        className: 'leaflet-dark-tooltip'
                    });

                    layer.on('click', function () {
                        self.resetHighlight();
                        self.selectedLayer = layer;
                        layer.setStyle({
                            weight: 3.5,
                            color: '#38bdf8', // sky-400 highlight
                            fillOpacity: 0.95
                        });
                        if (pred) {
                            self.selectedKelurahan = pred;
                        }
                    });
                }
            }).addTo(this.map);

            if (!this.boundsFitted && this.geojsonLayer.getLayers().length > 0) {
                try {
                    this.map.fitBounds(this.geojsonLayer.getBounds(), { padding: [20, 20] });
                    this.boundsFitted = true;
                } catch (e) {}
            }
        },

        resetHighlight() {
            if (this.selectedLayer && this.geojsonLayer) {
                this.geojsonLayer.resetStyle(this.selectedLayer);
                this.selectedLayer = null;
            }
        },

        formatDate(isoString) {
            if (!isoString) return '';
            try {
                const date = new Date(isoString);
                return date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) + ' WIB';
            } catch (e) {
                return isoString;
            }
        }
    };
}
</script>
@endpush
