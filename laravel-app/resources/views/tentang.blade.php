@extends('layouts.app')

@section('title', 'Tentang Data — ROB Predictor Jakarta Utara')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

    <!-- Header Judul -->
    <div class="border-b border-slate-800 pb-6">
        <div class="flex items-center space-x-2 text-xs text-sky-400 font-semibold uppercase tracking-wider mb-2">
            <span>Dokumentasi Sistem</span>
            <span>&bull;</span>
            <span>Versi Model: {{ $modelVersi }}</span>
        </div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Tentang Data & Metodologi Prediksi</h1>
        <p class="text-sm text-slate-400 mt-2 max-w-3xl leading-relaxed">
            Halaman ini menjelaskan sumber data, format fitur masukan, metodologi prediksi, keterbatasan analisis, dan penyangkalan tanggung jawab (disclaimer) untuk sistem prediksi banjir ROB Jakarta Utara.
        </p>
    </div>

    <!-- Alert Simulasi / Status Model -->
    @if($isSimulasi)
    <div class="p-5 rounded-2xl bg-amber-950/40 border border-amber-600/40 text-amber-200 space-y-2">
        <div class="flex items-center space-x-2">
            <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">STATUS SIMULASI (STUB)</span>
            <h3 class="font-bold text-amber-100 text-sm">Sistem Saat Ini Berjalan dalam Mode Simulasi</h3>
        </div>
        <p class="text-xs text-amber-300/90 leading-relaxed">
            Prediksi banjir ROB yang saat ini ditampilkan dihasilkan oleh microservice tiruan (<code>stub-v0</code>) berbasis aturan deterministik sederhana (elevasi, pasang, dan jarak pantai). Mode ini digunakan untuk memvalidasi integrasi antarmuka, peta GeoJSON, dan orkestrasi data sebelum model Machine Learning final dipasang pada Tahap 7.
        </p>
    </div>
    @endif

    <!-- 1. Karakteristik & Jenis Data -->
    <section class="space-y-4">
        <h2 class="text-xl font-bold text-white flex items-center space-x-2">
            <span class="w-2 h-5 rounded-full bg-blue-500 inline-block"></span>
            <span>1. Karakteristik & Jenis Data</span>
        </h2>
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 text-sm text-slate-300 leading-relaxed space-y-3">
            <p>
                Data yang disajikan pada dashboard ini adalah <strong>prediksi atas data historis (tahun 2026)</strong>, bukan prakiraan cuaca atau banjir waktu nyata (real-time forecast) di masa depan.
            </p>
            <p>
                Dataset harian mencakup <strong>31 kelurahan di Kota Administrasi Jakarta Utara</strong> selama rentang 1 Januari 2026 hingga 30 September 2026 (total 273 hari kalender lengkap = 8.463 baris observasi).
            </p>
        </div>
    </section>

    <!-- 2. Sumber Data & Kontrak Fitur Masukan -->
    <section class="space-y-4">
        <h2 class="text-xl font-bold text-white flex items-center space-x-2">
            <span class="w-2 h-5 rounded-full bg-sky-500 inline-block"></span>
            <span>2. Sumber Data & Kontrak Fitur Masukan</span>
        </h2>
        <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Nama Fitur</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Satuan</th>
                        <th class="px-4 py-3">Sumber Data</th>
                        <th class="px-4 py-3">Deskripsi & Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <tr class="hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-mono font-bold text-white">elevasi</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300">Statis</span></td>
                        <td class="px-4 py-3 font-semibold text-sky-400">meter (m)</td>
                        <td class="px-4 py-3">DEMNAS / Geospasial</td>
                        <td class="px-4 py-3 text-slate-400">Ketinggian rata-rata permukaan tanah kelurahan terhadap referensi permukaan laut.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-mono font-bold text-white">jarak_pantai</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300">Statis</span></td>
                        <td class="px-4 py-3 font-semibold text-sky-400">kilometer (km)</td>
                        <td class="px-4 py-3">Analisis Spasial GIS</td>
                        <td class="px-4 py-3 text-slate-400">Jarak Euclidean terdekat dari pusat kelurahan ke garis pantai utara Jakarta.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-mono font-bold text-white">curah_hujan</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded bg-blue-900/60 text-blue-300 border border-blue-700/40">Harian</span></td>
                        <td class="px-4 py-3 font-semibold text-sky-400">mm / hari</td>
                        <td class="px-4 py-3">Stasiun BMKG Kemayoran / Tanjung Priok</td>
                        <td class="px-4 py-3 text-slate-400">Akumulasi curah hujan harian per 24 jam. Nilai &ge; 0 mm/hari.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-mono font-bold text-white">pasang_tertinggi</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded bg-blue-900/60 text-blue-300 border border-blue-700/40">Harian</span></td>
                        <td class="px-4 py-3 font-semibold text-sky-400">meter (m)</td>
                        <td class="px-4 py-3">Prediksi Pasut BIG (Badan Informasi Geospasial)</td>
                        <td class="px-4 py-3 text-slate-400">Tinggi muka air laut pasang maksimum pada hari tersebut dari 2 stasiun referensi (Sunda Kelapa atau Kolinlamil).</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-mono font-bold text-white">gelombang_tertinggi</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded bg-blue-900/60 text-blue-300 border border-blue-700/40">Harian</span></td>
                        <td class="px-4 py-3 font-semibold text-sky-400">meter (m)</td>
                        <td class="px-4 py-3">BMKG Maritim Tanjung Priok</td>
                        <td class="px-4 py-3 text-slate-400">Tinggi gelombang laut maksimum di perairan Teluk Jakarta. Nilai &ge; 0 m.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 3. Metodologi Prediksi & Model Machine Learning -->
    <section class="space-y-4">
        <h2 class="text-xl font-bold text-white flex items-center space-x-2">
            <span class="w-2 h-5 rounded-full bg-cyan-500 inline-block"></span>
            <span>3. Model Machine Learning & Metrik Evaluasi</span>
        </h2>

        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-4 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-3">
                <div>
                    <span class="text-slate-400">Algoritma Utama:</span>
                    <span class="font-bold text-white text-sm ml-1">Random Forest Classifier (Ensemble Bagging)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        Model Aktif: model-v1
                    </span>
                    <span class="text-slate-500">| Periode Data Latih: 2023–2025</span>
                </div>
            </div>

            <!-- Perbandingan Model & Metrik -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pt-1">
                <div>
                    <h3 class="font-semibold text-slate-200 mb-2">Hasil Evaluasi Data Uji (Test Set 20% - Group Split):</h3>
                    <div class="overflow-x-auto rounded-lg border border-slate-800 bg-slate-950">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-900 text-slate-400 border-b border-slate-800">
                                <tr>
                                    <th class="px-3 py-2">Model</th>
                                    <th class="px-3 py-2">Akurasi</th>
                                    <th class="px-3 py-2">Recall (ROB)</th>
                                    <th class="px-3 py-2">F1-Score</th>
                                    <th class="px-3 py-2">ROC-AUC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                                <tr class="text-slate-400">
                                    <td class="px-3 py-2">Baseline (Logistic Regression)</td>
                                    <td class="px-3 py-2">65.8%</td>
                                    <td class="px-3 py-2">75.0%</td>
                                    <td class="px-3 py-2">0.6818</td>
                                    <td class="px-3 py-2">0.7060</td>
                                </tr>
                                <tr class="bg-emerald-950/20 text-emerald-300 font-semibold">
                                    <td class="px-3 py-2 flex items-center space-x-1">
                                        <span>Random Forest (v1)</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-400">Terpilih</span>
                                    </td>
                                    <td class="px-3 py-2">80.5%</td>
                                    <td class="px-3 py-2">85.0%</td>
                                    <td class="px-3 py-2">0.8095</td>
                                    <td class="px-3 py-2">0.8625</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-slate-200 mb-2">Tingkat Kontribusi Fitur (Feature Importance):</h3>
                    <div class="space-y-2 p-3 rounded-lg border border-slate-800 bg-slate-950">
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-300 font-medium">1. Pasang Tertinggi Laut</span>
                                <span class="text-sky-400 font-bold">44.16%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-sky-500 h-1.5 rounded-full" style="width: 44.16%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-300 font-medium">2. Gelombang Tertinggi</span>
                                <span class="text-sky-400 font-bold">23.19%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-sky-500 h-1.5 rounded-full" style="width: 23.19%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-300 font-medium">3. Jarak ke Garis Pantai</span>
                                <span class="text-sky-400 font-bold">14.05%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-sky-500 h-1.5 rounded-full" style="width: 14.05%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-300 font-medium">4. Elevasi Permukaan Tanah</span>
                                <span class="text-sky-400 font-bold">9.66%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-sky-500 h-1.5 rounded-full" style="width: 9.66%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-300 font-medium">5. Curah Hujan Harian</span>
                                <span class="text-sky-400 font-bold">8.94%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-sky-500 h-1.5 rounded-full" style="width: 8.94%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ambang Batas Risiko -->
        <h3 class="font-bold text-white text-sm pt-2">Ambang Batas Tingkat Risiko:</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span class="px-2 py-0.5 rounded font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 uppercase">Risiko Rendah</span>
                <p class="text-slate-300">Probabilitas model <strong>&lt; 35%</strong> (&lt; 0.35).</p>
                <p class="text-slate-400 text-[11px]">Kondisi pasang air laut normal dan elevasi wilayah aman dari genangan rob.</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span class="px-2 py-0.5 rounded font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 uppercase">Risiko Sedang</span>
                <p class="text-slate-300">Probabilitas model <strong>35% s.d. 64%</strong> (0.35 - 0.64).</p>
                <p class="text-slate-400 text-[11px]">Pasang air laut menengah dikombinasikan dengan jarak pantai yang dekat atau hujan lokal.</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span class="px-2 py-0.5 rounded font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 uppercase">Risiko Tinggi</span>
                <p class="text-slate-300">Probabilitas model <strong>&ge; 65%</strong> (&ge; 0.65).</p>
                <p class="text-slate-400 text-[11px]">Pasang maksimum tinggi (> 1.0 m) bertepatan dengan elevasi rendah (&le; 1.0 m) dan gelombang tinggi.</p>
            </div>
        </div>
    </section>

    <!-- 4. Keterbatasan Model -->
    <section class="space-y-4">
        <h2 class="text-xl font-bold text-white flex items-center space-x-2">
            <span class="w-2 h-5 rounded-full bg-purple-500 inline-block"></span>
            <span>4. Keterbatasan Analisis</span>
        </h2>
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 space-y-2 leading-relaxed">
            <ul class="list-disc list-inside space-y-2 text-slate-400">
                <li><strong class="text-slate-200">Resolusi Spasial Kelurahan:</strong> Analisis menggunakan unit agregasi tingkat kelurahan, sehingga variasi lokal seperti keberadaan tanggul RW atau pompa polder mikro tidak terefleksikan secara individual.</li>
                <li><strong class="text-slate-200">Ketiadaan Label Aktual 2026:</strong> Data tahun 2026 tidak memiliki ground-truth label banjir yang diverifikasi mandiri, sehingga akurasi empiris dihitung berdasarkan data latih/uji historis (2022–2025).</li>
                <li><strong class="text-slate-200">Stasiun Pasut Agregat:</strong> Nilai pasang air laut bersumber dari dua stasiun pengamatan utama di Sunda Kelapa dan Kolinlamil, sehingga kelurahan dalam stasiun yang sama berbagi nilai pasang yang identik.</li>
            </ul>
        </div>
    </section>

    <!-- 5. Disclaimer Resmi -->
    <section class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-3">
        <h3 class="text-base font-bold text-white flex items-center space-x-2">
            <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Pernyataan Penyangkalan (Disclaimer)</span>
        </h3>
        <p class="text-xs text-slate-400 leading-relaxed">
            Sistem dan website ini dikembangkan murni sebagai proyek akademik Capstone Machine Learning tanpa anggaran komersial. Hasil kalkulasi probabilitas dan peta kerentanan <strong>bukan merupakan peringatan dini atau rilis resmi dari instansi pemerintah</strong>. Untuk informasi resmi, kesiapsiagaan bencana, dan peringatan darurat pasang air laut, masyarakat diimbau untuk selalu merujuk kepada saluran resmi <strong>Badan Meteorologi, Klimatologi, dan Geofisika (BMKG)</strong> dan <strong>Badan Penanggulangan Bencana Daerah (BPBD) Provinsi DKI Jakarta</strong>.
        </p>
    </section>

</div>
@endsection

