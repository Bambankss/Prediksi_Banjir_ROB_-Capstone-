# Catatan dataset

Kamus data untuk proyek Website Prediksi Banjir ROB Jakarta Utara. Dibaca oleh tim, agen AI, dan menjadi sumber halaman Tentang Data (prd.md bagian 12).

> **Cara membaca berkas ini**
> - Isi tanpa tanda adalah ketentuan yang sudah ditetapkan di `prd.md` v0.4.
> - Tanda **`[ISI]`** berarti informasi belum tercatat dan harus diisi tim ML dari data nyata. Jangan diisi dari tebakan.
> - Bila ada perubahan, perbarui berkas ini dan sebutkan di bagian "Riwayat perubahan".

## Daftar berkas data

| Berkas | Lokasi | Dipakai oleh | Isi |
|---|---|---|---|
| `kelurahan.csv` | `data/` | Website dan ML | 31 kelurahan beserta fitur statis dan stasiun pasut |
| `data_latih.csv` | `data/` | ML saja | Data historis berlabel (kejadian ROB sekitar 2022-2025) |
| `data_harian2026.csv` | `data/` | Website (impor) | Data yang diprediksi, tahun 2026, tanpa label |
| `catatan_dataset.md` | `data/` | Tim dan agen AI | Kamus data (berkas ini) |
| `cek_dataset.py` | `ml/` | Tim ML | Pemeriksa dataset |

## Aturan format umum (semua CSV)

- Satu baris judul; UTF-8; pemisah koma (impor web/artisan juga menerima `;`); desimal titik.
- Tanggal ISO `YYYY-MM-DD` dalam **satu** kolom `tanggal` (tanpa kolom "Tanggal Fix"). `MM-DD-YYYY` ditolak karena ambigu.
- Nama kolom huruf kecil, tanpa spasi, tanpa satuan (satuan dicatat di berkas ini).
- Tanpa sel digabung dan tanpa baris kosong.
- `kode_wilayah` disimpan sebagai **teks** berformat `NN.NN.NN.NNNN` (di pandas: `dtype=str`).
- Tips Excel: blok kolom tanggal, Ctrl+1, Custom, ketik `yyyy-mm-dd`, lalu simpan sebagai CSV UTF-8. Bila perlu konversi di pandas: `pd.to_datetime(kolom, format="%m-%d-%Y").dt.strftime("%Y-%m-%d")`, lalu periksa tanggal yang harinya lebih dari 12.

## Kamus kolom

### `kelurahan.csv` (31 baris)

| Kolom | Isi | Satuan |
|---|---|---|
| `kode_wilayah` | Kode Kemendagri, teks `NN.NN.NN.NNNN` | - |
| `kelurahan` | Nama kelurahan (di database dipetakan ke kolom `nama`) | - |
| `kecamatan` | Nama kecamatan | - |
| `elevasi` | Ketinggian | m |
| `jarak_pantai` | Jarak ke garis pantai | **km** |
| `stasiun_pasut` | `Sunda Kelapa` atau `Kolinlamil` | - |

### `data_latih.csv` (satu baris = satu kelurahan pada satu tanggal)

| Kolom | Isi | Satuan / nilai |
|---|---|---|
| `tanggal` | Tanggal ISO | `YYYY-MM-DD` |
| `kode_wilayah` | Kunci kelurahan | teks |
| `kelurahan` | Nama, untuk dibaca manusia | - |
| `elevasi` | Dari `kelurahan.csv` | m |
| `jarak_pantai` | Dari `kelurahan.csv` | km |
| `curah_hujan` | Hujan pada tanggal itu | mm/hari |
| `pasang_tertinggi` | Pasang dari stasiun kelurahan itu | m |
| `gelombang_tertinggi` | Gelombang tertinggi | m |
| `is_banjir` | Label | 0 atau 1 |

Kolom opsional: `sumber_label` (mis. `laporan_rw`, `berita`) untuk menguji sensitivitas.

### `data_harian2026.csv` (satu baris = satu kelurahan pada satu tanggal)

`kode_wilayah, tanggal, curah_hujan, pasang_tertinggi, gelombang_tertinggi`

- Tanpa label dan tanpa fitur statis (elevasi dan jarak pantai sudah ada di tabel `kelurahan` website).
- Satuan harus sama persis dengan `data_latih.csv`.
- Tiap tanggal harus punya 31 baris.

## Sumber fitur

Satuan dan urutan fitur model: `elevasi, jarak_pantai, curah_hujan, pasang_tertinggi, gelombang_tertinggi`.

| Fitur | Jenis | Satuan | Sumber | Resolusi / cakupan | Periode | Catatan |
|---|---|---|---|---|---|---|
| `elevasi` | statis per kelurahan | m | `[ISI]` | `[ISI]` | - | `[ISI]` |
| `jarak_pantai` | statis per kelurahan | km | `[ISI]` | `[ISI]` | - | `[ISI]` (cara pengukuran) |
| `curah_hujan` | harian | mm/hari | `[ISI]` | `[ISI]` | `[ISI]` | `[ISI]` |
| `pasang_tertinggi` | harian, menurut stasiun | m | `[ISI]` (PRD memberi contoh: prediksi pasut BIG untuk dua stasiun) | dua stasiun | `[ISI]` | datum: lihat bagian "Datum pasang" |
| `gelombang_tertinggi` | harian | m | `[ISI]` | `[ISI]` | `[ISI]` | `[ISI]` |

Lisensi dan atribusi tiap sumber: `[ISI]`.

## Rentang data

| Berkas | Tanggal awal | Tanggal akhir | Jumlah baris | Jumlah `is_banjir = 1` | Jumlah `is_banjir = 0` |
|---|---|---|---|---|---|
| `data_latih.csv` | 2023-01-01 | 2025-12-08 | 414 | 207 | 207 |
| `data_harian2026.csv` | 2026-01-01 | 2026-09-30 | 8.463 (31 x 273 hari) | tidak ada label | tidak ada label |

Pembagian data latih/uji: `GroupShuffleSplit` (80% latih, 20% uji) berdasarkan **tanggal** (tanpa kebocoran tanggal). Latih: 332 baris, Uji: 82 baris.
Model Aktif: `RandomForestClassifier` (`model-v1`), Akurasi Uji 80.5%, Recall 85.0%, F1-Score 81.0%, ROC-AUC 86.25%. Ambang risiko: Rendah (< 0.35), Sedang (0.35 - 0.64), Tinggi (>= 0.65).

## Kode wilayah

- Format: `NN.NN.NN.NNNN`, disimpan sebagai teks.
- Sumber resmi kode Kemendagri untuk 31 kelurahan: `[ISI: nama dokumen/lampiran resmi, tanggal akses, tautan]`
- Kode **harus** diambil dari lampiran resmi, bukan dari ingatan atau tebakan (keputusan terbuka O6).
- Kunci kelurahan di semua berkas dan tabel adalah `kode_wilayah`, bukan nama dan bukan kode pos.
- Kode di `kelurahan.csv`, `data_latih.csv`, `data_harian2026.csv`, dan `kelurahan.geojson` harus identik (31 kode yang sama persis).

## Aturan label

- `is_banjir = 1` bila ada laporan ROB di kelurahan itu pada tanggal itu; selain itu `0`.
- Beberapa laporan pada kelurahan dan hari yang sama menjadi **satu** baris.
- Jumlah RT tergenang tidak dicatat dan tidak dipakai. Unit wilayah adalah kelurahan.
- Laporan yang hanya menyebut kecamatan **tidak** disebar ke semua kelurahannya; pakai laporan tingkat kelurahan atau tandai terpisah.
- Sumber laporan kejadian: `[ISI: mis. laporan RW, berita, BPBD; sebutkan]`
- Hal lain yang dikecualikan dari label 1: `[ISI]`
- Label tidak dikirim ke layanan prediksi dan tidak ada di `data_harian2026.csv`.

## Pembuatan baris 0

- Ketentuan: baris `is_banjir = 0` idealnya dibuat dari **semua kelurahan x semua tanggal** dalam rentang data, bukan sampel pilihan, agar proporsi banjir tidak bias (keputusan terbuka O7). Baris 0 tidak boleh dibuang dari berkas.
- Cara baris 0 sebenarnya dibuat: `[ISI: semua kelurahan x semua tanggal, atau sebagian?]`
- Bila hanya sebagian: jelaskan aturan pengambilannya di sini dan nyatakan di halaman Tentang Data (probabilitas model dapat bias).
- Skrip/notebook pembuat baris 0: `[ISI]`
- Ketidakseimbangan kelas ditangani saat melatih (mis. pemberat kelas) dan **hanya** pada data latih.

## Datum pasang dan stasiun

- Stasiun pasut hanya dua: `Sunda Kelapa` dan `Kolinlamil`. Tiap kelurahan memakai salah satunya; nilai `pasang_tertinggi` pada berkas data sudah dipilih sesuai stasiun itu.
- Datum pasang (mis. MSL atau LAT): `[ISI]` (keputusan terbuka O2). Harus **sama** pada data latih dan data 2026.
- Pemetaan kelurahan ke stasiun: lihat kolom `stasiun_pasut` di `kelurahan.csv`; dasar pemilihannya: `[ISI]`

## Pemeriksaan dataset

`ml/cek_dataset.py` dijalankan pada `data/data_latih.csv` sebelum melatih. Semua pemeriksaan harus lolos:

- tanggal berformat ISO dan hanya satu kolom tanggal;
- `kode_wilayah` berformat benar dan seluruhnya cocok dengan `kelurahan.csv`;
- tidak ada duplikat (`tanggal`, `kode_wilayah`);
- tiap tanggal punya tepat 31 baris;
- tidak ada nilai kosong; `is_banjir` hanya 0 atau 1;
- satuan masuk akal (mis. `jarak_pantai` dalam km, curah hujan tidak negatif);
- jumlah baris label 1 dan 0 dicetak, dan tiap kejadian di daftar laporan bernilai 1;
- seluruh 31 kelurahan muncul setidaknya sekali.

Hasil pemeriksaan terakhir (tanggal dan ringkasan): `[ISI]`

## Keterbatasan

Ketentuan dari PRD:

- Data 2026 tidak berlabel, sehingga akurasinya tidak bisa dihitung. Pengujian memakai pembagian waktu (mis. uji di 2025).
- Hasil yang tampil adalah prediksi atas data historis (2026), bukan prakiraan masa depan.
- Label kejadian berasal dari laporan, sehingga ada bias pelaporan (kejadian yang tidak dilaporkan tercatat 0).
- Nilai fitur harian (pasang, gelombang, hujan) dapat sama di banyak kelurahan, sehingga peta cenderung "naik-turun bersama". Analisis variasi fitur dilakukan tim ML.
- Laporan tingkat kecamatan tidak disebar ke kelurahan.
- Hasil bukan peringatan resmi; rujuk BMKG/BPBD.

Keterbatasan khusus data ini (periode bolong, kualitas sumber, dll.): `[ISI]`

## Riwayat perubahan

| Tanggal | Perubahan | Oleh |
|---|---|---|
| 2026-11-02 | Kerangka awal disusun dari `prd.md` v0.4 | - |
