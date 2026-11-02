# PRD: Website Prediksi Banjir ROB Jakarta Utara

Versi: 0.4 · Fokus dokumen ini: **website (Laravel + SQLite) di lingkungan Laragon**. Dataset pelatihan dan model ML dikerjakan di jalur terpisah dan baru dihubungkan di Tahap 7.

Perubahan dari v0.3 (rincian di Lampiran B): struktur dan nama berkas data disesuaikan dengan berkas nyata (`kelurahan.csv`, `data_latih.csv`, `data_harian2026.csv`); satuan `jarak_pantai` ditetapkan km; `stasiun_pasut` hanya dua nilai (Sunda Kelapa, Kolinlamil); label bernama `is_banjir`; format `kode_wilayah` dan tanggal ISO diwajibkan; ditambah acuan dataset untuk tim ML (5.7) serta dokumen pendukung dan instruksi untuk agen AI (bagian 20).

Perubahan dari v0.2 ke v0.3: lingkungan pengembangan Laragon; 15 perubahan sistem (Lampiran A); tahapan pengembangan Tahap 1–7; pemisahan data operasional website dan dataset pelatihan ML.

Definisi prioritas: **Must** = harus selesai sebelum website dianggap siap demonstrasi. **Should** = penting, tetapi boleh menyusul setelah fungsi inti berjalan. **Could** = opsional.

## 1. Ringkasan

Website publik yang menampilkan hasil prediksi banjir ROB (banjir pasang air laut) untuk 31 kelurahan di Jakarta Utara pada tanggal yang dipilih pengguna, dalam bentuk peta interaktif berwarna. Admin mengelola data masukan yang diprediksi (CRUD), memantau kelengkapan data dan status prediksi, dan dapat menghitung ulang prediksi. Proyek ini adalah Capstone Machine Learning tanpa anggaran: semua teknologi harus gratis.

### Tujuan
- Pengguna memilih tanggal, menekan "Cari", dan melihat tingkat risiko ROB tiap kelurahan di peta.
- Pengguna dapat mengeklik kelurahan untuk melihat detail hasil dan nilai fitur.
- Admin dapat menambah, melihat, mengubah, dan menghapus data harian, melihat kelurahan yang belum punya data, melihat status prediksi per tanggal, dan menghitung ulang per tanggal.
- Website dapat dikerjakan penuh dan didemokan **sebelum** dataset pelatihan dan model ML selesai, memakai layanan prediksi tiruan (stub) yang diberi penanda "simulasi" yang jelas.
- Seluruh sistem berjalan dengan SQLite dan tanpa layanan berbayar.

### Bukan tujuan (versi ini)
- Prakiraan masa depan secara real-time. Data yang tersedia baru tanggal-tanggal lampau (2026), jadi hasilnya adalah prediksi atas data yang sudah berlalu (historis).
- Akun atau registrasi pengguna umum.
- Unduh hasil publik (CSV/PNG/PDF). Hasil tanpa konteks mudah disalahgunakan sebagai peringatan resmi, dan fitur ini memakan waktu yang lebih baik dipakai untuk hal lain.
- Menyusun dataset pelatihan, membuat label ROB, dan melatih model dari antarmuka web. Itu pekerjaan tim ML, di luar website.
- Notifikasi, aplikasi mobile, dan pengambilan data otomatis dari API.
- Pengganti peringatan resmi BMKG/BPBD.

## 2. Keputusan

| # | Keputusan |
|---|---|
| D1 | Dashboard publik tanpa login. Tidak ada registrasi pengguna. |
| D2 | Login hanya untuk admin, lewat alamat `/admin/login`; tidak ada tombol login di navigasi publik. |
| D3 | Setelah login, muncul tombol "Admin" di navigasi; menekannya membuka menu "Dashboard admin", "Kelola data", dan "Logout". |
| D4 | Pemilihan tanggal memakai date picker plus tombol **"Cari"**. |
| D5 | Database: SQLite, dimiliki **hanya oleh Laravel**. |
| D6 | Admin hanya mengelola **data masukan yang diprediksi**, bukan dataset pelatihan atau label. |
| D7 | Data latih (ML): `data_latih.csv`, kejadian ROB sekitar 2022–2025 (sekitar 400-an kejadian terlaporkan), disusun manual oleh tim ML. Data prediksi: `data_harian2026.csv`, tahun 2026 (tanpa label). |
| D8 | Model dilatih terpisah dari website dan diekspor sebagai berkas. |
| D9 | Website dibangun dengan **Laravel**. |
| D10 | Prediksi dihitung oleh **layanan Python kecil (FastAPI)** yang dipanggil Laravel lewat HTTP (opsi A). Cadangan: Laravel menjalankan skrip Python (opsi B, bagian 5.6). |
| D11 | Fitur unduh publik tidak dibuat. |
| D12 | Lingkungan pengembangan: **Laragon** (Windows). Lihat bagian 3. |
| D13 | Data operasional website dipisah dari dataset pelatihan ML. Lihat bagian 4. |
| D14 | Selama memakai stub, semua hasil wajib ditandai **simulasi** di database, API, dan antarmuka. |
| D15 | Kunci kelurahan di semua berkas dan tabel adalah `kode_wilayah` (kode Kemendagri, disimpan sebagai **teks** berformat `NN.NN.NN.NNNN`), bukan nama dan bukan kode pos. |
| D16 | Format data: UTF-8, pemisah `,` (impor web/artisan juga menerima `;`), desimal titik, tanggal ISO `YYYY-MM-DD`. Format `MM-DD-YYYY` ditolak karena ambigu. |
| D17 | Stasiun pasut hanya dua: `Sunda Kelapa` dan `Kolinlamil`. Tiap kelurahan memakai salah satunya; nilai pasang pada berkas data sudah dipilih sesuai stasiun itu. |
| D18 | Penjelasan data (sumber, satuan, aturan label, cara baris 0 dibuat) dicatat di `data/catatan_dataset.md`; agen AI diarahkan membacanya lewat bagian 20. |

### Keputusan yang masih terbuka
| # | Pertanyaan | Default sementara |
|---|---|---|
| O1 | Keluaran model: 3 tingkat atau biner? | Model biner menghasilkan probabilitas, lalu 3 tingkat (Rendah/Sedang/Tinggi) diturunkan dari ambang. Website mendukung keduanya karena yang disimpan adalah `probabilitas` dan `tingkat`. |
| O2 | Datum pasang (mis. MSL atau LAT) | Dicatat tim ML di `catatan_dataset.md`; harus sama pada data latih dan data 2026. Satuan fitur lain sudah ditetapkan (5.1). |
| O3 | Sumber batas poligon kelurahan (GeoJSON) | Portal data resmi atau OpenStreetMap; cek lisensi. Wajib lolos validasi 31 kode wilayah (bagian 10). |
| O4 | Hosting setelah demo | Demo lokal lewat Laragon cukup untuk capstone. Bila di-hosting, cek ketentuan layanan gratis dan dukungan dua proses (Laravel + Python). |
| O5 | Aturan label ROB | Diputuskan tim ML; tidak memengaruhi website selama kontrak fitur dan keluaran tetap. Sudah disepakati: jumlah RT tergenang tidak dicatat; wilayah memakai unit kelurahan. |
| O6 | Sumber resmi kode wilayah Kemendagri untuk 31 kelurahan | Ambil dari lampiran resmi (jangan dari ingatan atau tebakan); simpan sumbernya di `catatan_dataset.md`. |
| O7 | Apakah baris `is_banjir = 0` di `data_latih.csv` dibuat dari semua kelurahan × semua tanggal? | Default: ya (lihat 5.7). Bila hanya sebagian, catat dan nyatakan di Tentang Data. |

## 3. Lingkungan pengembangan: Laragon

Laragon dipakai untuk pengembangan dan demonstrasi lokal di Windows. Laragon **bukan** tempat hosting produksi.

| Komponen | Pengaturan |
|---|---|
| PHP, Composer, Node.js | Dari Laragon. Pilih versi PHP yang memenuhi syarat Laravel yang dipakai (cek dokumentasi Laravel). |
| Web server | Apache atau Nginx bawaan Laragon. Virtual host otomatis, mis. `http://rob-predictor.test` (atur `APP_URL`). |
| Database | **SQLite**. MySQL bawaan Laragon tidak dipakai. Pastikan ekstensi PHP `pdo_sqlite` dan `sqlite3` aktif (menu Laragon → PHP → Extensions). |
| Folder proyek | Di bawah `C:\laragon\www\rob-predictor\` (struktur di bagian 11). |
| Layanan Python | Python dipasang terpisah (cek apakah sudah ada di mesin), dijalankan di virtualenv: `python -m venv .venv`, aktifkan, `pip install -r requirements.txt`, lalu `uvicorn app:app --host 127.0.0.1 --port 8001`. |
| Aset frontend | `npm install` dan `npm run dev` (Vite) atau `npm run build`, lewat terminal Laragon. |
| Artisan | Semua perintah `php artisan ...` dijalankan dari terminal Laragon di folder `laravel-app`. |
| Skrip bantu | `jalankan-ml-service.bat` untuk menyalakan layanan Python dalam satu klik. |

Catatan khusus Windows/Excel:
- CSV dari Excel berlokal Indonesia sering memakai pemisah titik koma dan koma sebagai desimal. Impor menerima pemisah `,` atau `;`, tetapi **desimal wajib titik**; nilai berkoma ditolak dengan pesan jelas.
- Simpan CSV dengan encoding UTF-8.

## 4. Arsitektur data: pisahkan data operasional dan dataset pelatihan

Keduanya punya tujuan berbeda dan tidak boleh bercampur.

| Data | Digunakan untuk | Lokasi |
|---|---|---|
| Data harian yang akan diprediksi | Masukan untuk model | SQLite |
| Data kelurahan | Karakteristik geografis wilayah | SQLite |
| Hasil prediksi dan statusnya | Ditampilkan di peta dan admin | SQLite |
| Riwayat impor dan log aktivitas | Jejak audit | SQLite |
| Dataset historis beserta label ROB (`data_latih.csv`) | Melatih dan mengevaluasi model | Folder `data/` (dipakai skrip di `ml/`) |
| Kamus data (`catatan_dataset.md`) | Dokumentasi dataset untuk tim, agen AI, dan halaman Tentang Data | Folder `data/` |
| Berkas model `.joblib` + metadata `.json` | Menjalankan prediksi | `ml-service/model_artifacts/` |

Aturan:
1. Admin hanya mengelola data masukan yang diperlukan website. Pembuatan label dan pelatihan model dilakukan terpisah oleh tim ML.
2. Dataset pelatihan **tidak** masuk database web.
3. Pekerjaan website tidak menunggu dataset ML. Selama dataset belum selesai, website memakai stub; saat model asli siap, cukup mengganti isi `ml-service/model_artifacts/` dan menjalankan hitung ulang (Tahap 7).
4. **Satu-satunya hal yang bisa memaksa perubahan database web** adalah perubahan *daftar fitur masukan* (menambah atau mengurangi fitur). Karena itu daftar fitur diperlakukan sebagai kontrak (bagian 5.1): didaftarkan di `model_vN.json`, dicek oleh layanan Python saat start, dan dicocokkan Laravel lewat `/health`. Perubahan label, jumlah data latih, algoritma, atau metrik **tidak** menyentuh database web.

## 5. Model ML dan layanan prediksi

### 5.1 Kontrak fitur masukan (urutan dan satuan harus sama dengan data latih)
| Fitur | Jenis | Satuan |
|---|---|---|
| `elevasi` | statis per kelurahan | m |
| `jarak_pantai` | statis per kelurahan | **km** |
| `curah_hujan` | harian | mm/hari |
| `pasang_tertinggi` | harian, menurut stasiun kelurahan | m (datum dicatat di `catatan_dataset.md`) |
| `gelombang_tertinggi` | harian | m |

Label pelatihan: kolom `is_banjir` (0 = tidak ada laporan ROB di kelurahan itu pada tanggal itu, 1 = ada). Label **tidak** dikirim ke layanan prediksi dan tidak ada di `data_harian2026.csv`. Urutan fitur untuk model: `elevasi, jarak_pantai, curah_hujan, pasang_tertinggi, gelombang_tertinggi`.

### 5.2 Artefak model
- `ml-service/model_artifacts/model_v1.joblib`: pipeline scikit-learn yang sudah menyertakan praproses. Parameter praproses dihitung dari data latih saja.
- `ml-service/model_artifacts/model_v1.json`: metadata (versi, tanggal latih, daftar fitur berurutan, metrik uji, ambang tingkat, periode data latih/uji).

### 5.3 Layanan prediksi Python (FastAPI)
Prinsip: **stateless dan tanpa akses database**. Menerima baris fitur, mengembalikan hasil. Hanya Laravel yang menulis ke SQLite.

Keamanan: mendengarkan `127.0.0.1` saja; setiap permintaan wajib membawa header `X-Service-Token` yang cocok dengan `.env`.

| Metode | Rute | Fungsi |
|---|---|---|
| GET | `/health` | Status, mode, versi model, daftar fitur |
| POST | `/predict` | Prediksi untuk satu atau banyak baris |

`GET /health`:
```json
{ "status": "ok", "mode": "stub", "simulasi": true, "model_versi": "stub-v0",
  "fitur": ["elevasi","jarak_pantai","curah_hujan","pasang_tertinggi","gelombang_tertinggi"] }
```

`POST /predict` permintaan:
```json
{ "rows": [ { "kode_wilayah": "…", "elevasi": 0.4, "jarak_pantai": 0.35,
              "curah_hujan": 12.5, "pasang_tertinggi": 1.2, "gelombang_tertinggi": 0.9 } ] }
```
Respons:
```json
{ "model_versi": "stub-v0", "mode": "stub", "simulasi": true,
  "hasil": [ { "kode_wilayah": "…", "probabilitas": 0.82, "tingkat": "tinggi" } ] }
```
Aturan layanan:
- Setiap hasil membawa `kode_wilayah`; **pencocokan hasil dilakukan dengan kode wilayah, bukan urutan**.
- `mode` bernilai `stub` atau `model`; `simulasi` bernilai `true` bila stub.
- `tingkat` dihitung di layanan memakai ambang dari `model_vN.json` (atau aturan stub).
- Galat: `401` (token salah), `422` (validasi gagal: fitur hilang, bukan angka, kode wilayah ganda), `500` (galat internal). Bentuk galat: `{ "status": "error", "kode": "…", "pesan": "…" }`.
- Mode stub: `PREDICTOR_MODE=stub` memakai aturan sederhana (mis. dari elevasi dan pasang). Versi diberi nama `stub-v0` agar tidak pernah tertukar dengan versi model asli.

### 5.4 Alur melatih model (di luar website)
1. Jalankan `ml/cek_dataset.py` pada `data/data_latih.csv` dan pastikan semua pemeriksaan lolos (5.7). Lalu latih di folder `ml/` (notebook atau `train.py`).
2. Pembagian data berdasarkan waktu, bukan acak (mis. latih 2022–2024, uji 2025). Data 2026 tidak berlabel sehingga akurasinya tidak bisa dihitung; nyatakan di Tentang Data.
3. Metrik utama: recall kelas ROB, presisi, F1, confusion matrix.
4. Ekspor `model_vN.joblib` + `model_vN.json` ke `ml-service/model_artifacts/`, ubah versi aktif di `.env` layanan, restart layanan.
5. Setelah model berganti, ubah `MODEL_VERSI_AKTIF` di Laravel dan jalankan `php artisan prediksi:hitung`.

### 5.5 `PredictorClient` dan `PrediksiService` di Laravel
- `PredictorClient`: hanya urusan HTTP (facade `Http`), timeout, token, `health()` dan `predict()`.
- `PrediksiService`: orkestrasi hitung ulang satu tanggal (bagian 8).
- Pengiriman per batch (satu tanggal = 31 baris per permintaan).
- Bila layanan tidak aktif: data tetap tersimpan, status prediksi menjadi `belum_dihitung` atau `kedaluwarsa`, dan hasil lama yang masih valid **tetap dipertahankan** (bagian 8).

### 5.6 Cadangan (opsi B)
Bila menjalankan dua program menyulitkan, `PredictorClient` diganti implementasi yang menjalankan `python predict.py` lewat `Illuminate\Support\Facades\Process` dengan JSON yang sama. Kekurangannya model dimuat ulang tiap panggilan. Karena kontrak JSON sama, tidak ada bagian lain yang berubah.

### 5.7 Dataset pelatihan dan berkas data (acuan tim ML)

Berkas dan pemakainya:

| Berkas | Lokasi | Dipakai oleh | Isi |
|---|---|---|---|
| `kelurahan.csv` | `data/` | Website dan ML | 31 kelurahan beserta fitur statis dan stasiun pasut |
| `data_latih.csv` | `data/` | ML saja | Data historis berlabel (2022–2025) |
| `data_harian2026.csv` | `data/` | Website (impor) | Data yang diprediksi, tanpa label |
| `catatan_dataset.md` | `data/` | Tim dan agen AI | Kamus data |
| `cek_dataset.py` | `ml/` | Tim ML | Pemeriksa dataset |

Aturan umum semua CSV: satu baris judul; UTF-8; pemisah koma; desimal titik; tanggal `YYYY-MM-DD`; nama kolom huruf kecil tanpa spasi dan tanpa satuan (satuan dicatat di kamus data); tanpa sel digabung atau baris kosong; `kode_wilayah` sebagai teks (di pandas: `dtype=str`).

**`kelurahan.csv`** (31 baris)
| Kolom | Isi | Satuan |
|---|---|---|
| `kode_wilayah` | kode Kemendagri, teks `NN.NN.NN.NNNN` | – |
| `kelurahan` | nama kelurahan | – |
| `kecamatan` | nama kecamatan | – |
| `elevasi` | ketinggian | m |
| `jarak_pantai` | jarak ke garis pantai | km |
| `stasiun_pasut` | `Sunda Kelapa` atau `Kolinlamil` | – |

**`data_latih.csv`** (satu baris = satu kelurahan pada satu tanggal)
| Kolom | Isi | Satuan / nilai |
|---|---|---|
| `tanggal` | satu kolom tanggal ISO (tanpa kolom "Tanggal Fix") | `YYYY-MM-DD` |
| `kode_wilayah` | kunci kelurahan | teks |
| `kelurahan` | nama (untuk dibaca manusia) | – |
| `elevasi` | dari `kelurahan.csv` | m |
| `jarak_pantai` | dari `kelurahan.csv` | km |
| `curah_hujan` | hujan pada tanggal itu | mm/hari |
| `pasang_tertinggi` | pasang dari stasiun kelurahan itu | m |
| `gelombang_tertinggi` | gelombang tertinggi | m |
| `is_banjir` | label | 0 atau 1 |

**`data_harian2026.csv`** (satu baris = satu kelurahan pada satu tanggal)
`kode_wilayah, tanggal, curah_hujan, pasang_tertinggi, gelombang_tertinggi`. Tanpa label dan tanpa fitur statis (elevasi dan jarak pantai sudah ada di tabel `kelurahan` website). Satuan harus sama persis dengan `data_latih.csv`. Tiap tanggal harus punya 31 baris.

Aturan label dan baris 0:
- `is_banjir = 1` bila ada laporan ROB di kelurahan itu pada tanggal itu; selain itu 0. Beberapa laporan pada kelurahan dan hari yang sama menjadi **satu** baris.
- Jumlah RT tergenang tidak dicatat dan tidak dipakai. Wilayah memakai unit kelurahan.
- Laporan yang hanya menyebut kecamatan **tidak** disebar ke semua kelurahannya; pakai laporan tingkat kelurahan atau tandai terpisah.
- Baris 0 idealnya dibuat dari **semua kelurahan × semua tanggal** dalam rentang data, bukan sampel pilihan, agar proporsi banjir tidak bias (O7). Jangan membuang baris 0 dari berkas.
- Ketidakseimbangan kelas ditangani saat melatih (mis. pemberat kelas) dan hanya pada data latih.
- Opsional: kolom `sumber_label` (mis. `laporan_rw`, `berita`) untuk menguji sensitivitas.

Pembagian data: berdasarkan **tanggal**, bukan baris acak. Semua kelurahan pada satu tanggal berada di sisi yang sama (latih atau uji), karena mereka berbagi pasang dan gelombang yang sama.

Pemeriksaan wajib `ml/cek_dataset.py` (dijalankan sebelum melatih):
- tanggal berformat ISO dan hanya satu kolom tanggal;
- `kode_wilayah` berformat benar dan seluruhnya cocok dengan `kelurahan.csv`;
- tidak ada duplikat (`tanggal`, `kode_wilayah`);
- tiap tanggal punya tepat 31 baris;
- tidak ada nilai kosong; `is_banjir` hanya 0 atau 1;
- satuan masuk akal (mis. `jarak_pantai` dalam km, curah hujan tidak negatif);
- jumlah baris label 1 dan 0 dicetak, dan tiap kejadian di daftar laporan bernilai 1;
- seluruh 31 kelurahan muncul setidaknya sekali.

Format tanggal di Excel: blok kolom, Ctrl+1, Custom, ketik `yyyy-mm-dd`, lalu simpan sebagai CSV UTF-8. Bila tetap tidak bisa, konversi dengan `pd.to_datetime(kolom, format="%m-%d-%Y").dt.strftime("%Y-%m-%d")` (format ditulis eksplisit) dan periksa tanggal yang harinya lebih dari 12.

Kerangka `data/catatan_dataset.md`:
```
# Catatan dataset
## Sumber fitur      (tabel: fitur | sumber | satuan | periode)
## Rentang data      (tanggal awal-akhir, jumlah baris, jumlah label 1)
## Kode wilayah      (sumber resmi kode Kemendagri)
## Aturan label      (apa itu is_banjir=1; apa yang dikecualikan)
## Pembuatan baris 0 (semua kelurahan x semua tanggal, atau sebagian?)
## Datum pasang dan stasiun (Sunda Kelapa, Kolinlamil)
## Keterbatasan      (periode bolong, laporan tingkat kecamatan, dll.)
```

## 6. Fitur

### 6.1 Publik
| ID | Fitur | Prioritas |
|---|---|---|
| P1 | Peta Jakarta Utara, 31 kelurahan berwarna sesuai tingkat risiko | Must |
| P2 | Date picker + tombol "Cari"; tanggal dibatasi ke tanggal yang punya hasil | Must |
| P3 | Legenda warna (berteks) dan keterangan jenis data ("historis") | Must |
| P4 | Panel detail kelurahan (nama, kecamatan, tingkat, probabilitas, lima fitur) | Must |
| P5 | **Status tampilan: loading, kosong, galat** (bagian 9) | Must |
| P6 | **Banner "hasil simulasi"** bila hasil berasal dari stub | Must |
| P7 | **Versi model dan waktu pembaruan** ditampilkan | Should |
| P8 | Ringkasan tanggal terpilih (jumlah kelurahan per tingkat) | Should |
| P9 | Pencarian kelurahan dan filter kecamatan/tingkat | Should |
| P10 | Halaman Tentang Data yang jelas (bagian 12) | Should |
| P11 | Landing page dengan statistik ringkas | Could |

### 6.2 Admin
| ID | Fitur | Prioritas |
|---|---|---|
| A1 | Login/logout; tombol "Admin" setelah login | Must |
| A2 | Dashboard admin: ringkasan kelengkapan, status prediksi, model aktif, status layanan Python, impor terakhir | Should |
| A3 | Daftar tanggal dengan **kelengkapan data** (n/31) dan **status prediksi** per tanggal (terpisah) | Must |
| A4 | Halaman per tanggal: tabel 31 kelurahan yang bisa diedit sekaligus (tambah, ubah, hapus) | Must |
| A5 | **Daftar kelurahan yang belum memiliki data** pada tanggal itu | Must |
| A6 | **Hitung ulang per tanggal** (tombol) | Must |
| A7 | "Isi cepat": isi gelombang/pasang sekali, disalin ke kelurahan terkait (pasang menurut `stasiun_pasut`) | Should |
| A8 | Validasi input (bagian 13) | Must |
| A9 | Prediksi dihitung otomatis saat data disimpan | Must |
| A10 | Impor CSV lewat perintah artisan | Must |
| A11 | **Riwayat impor dan laporan hasil impor** (halaman admin) | Should |
| A12 | Penanda **simulasi** pada status prediksi bila dari stub | Must |
| A13 | Log aktivitas admin | Should |
| A14 | Impor CSV lewat halaman web dengan pratinjau | Could |
| A15 | Ekspor data ke CSV (khusus admin) | Could |

Data statis kelurahan (elevasi, jarak pantai, stasiun pasut, batas wilayah) tidak diedit lewat web; diisi lewat seeder.

## 7. Alur pengguna

### 7.1 Publik
1. Pengunjung membuka `/peta`. Peta tampil dengan kelurahan abu-abu dan pesan "Pilih tanggal lalu tekan Cari" (keadaan kosong awal).
2. Pengunjung memilih tanggal dari daftar tanggal yang punya hasil.
3. Pengunjung menekan **Cari**; tombol dinonaktifkan dan indikator loading muncul.
4. Halaman memanggil `GET /data/prediksi?tanggal=…`.
5. Laravel mengembalikan hasil tersimpan untuk versi model aktif. Hasil lengkap 31 kelurahan, termasuk `meta` (versi, simulasi, waktu pembaruan).
6. Peta diwarnai per tingkat; legenda, ringkasan, versi model, dan waktu pembaruan diperbarui. Banner simulasi tampil bila `meta.simulasi = true`.
7. Pengunjung mengeklik kelurahan; panel detail muncul.
8. Pengunjung mengganti tanggal dan menekan **Cari** lagi.

### 7.2 Admin
1. Admin membuka `/admin/login` (tidak ditautkan di navigasi publik).
2. Login berhasil → navigasi menampilkan tombol **Admin** (menu Dashboard admin, Kelola data, Logout; logout lewat form POST + CSRF).
3. Dashboard admin dan "Kelola data" menampilkan tabel tanggal: tanggal, kelengkapan (n/31), status prediksi, penanda simulasi, waktu dihitung, aksi (Edit, Hitung ulang).
4. Halaman per tanggal: daftar **kelurahan yang belum punya data** di bagian atas, lalu tabel 31 baris (curah hujan, pasang, gelombang). Admin mengedit dan menyimpan sekaligus; "Isi cepat" tersedia.
5. Saat disimpan: validasi → simpan → coba hitung ulang prediksi tanggal itu. Bila layanan Python mati, data tetap tersimpan dan status prediksi menjadi `kedaluwarsa` atau `belum_dihitung`.
6. Admin dapat menekan **Hitung ulang** per tanggal kapan saja (aktif hanya bila data lengkap 31/31).
7. Halaman **Impor** menampilkan riwayat impor dan laporan tiap impor (jumlah baris, berhasil, galat, daftar galat per baris).

## 8. Logika prediksi dan status

### 8.1 Pemisahan kelengkapan data dan status prediksi
- **Kelengkapan data** dihitung dari tabel `data_harian`: jumlah kelurahan yang punya data pada tanggal itu dibanding 31.
- **Status prediksi** disimpan terpisah di tabel `status_prediksi` per (tanggal, versi model).

Nilai status prediksi:
| Status | Arti |
|---|---|
| `belum_dihitung` | Belum pernah dihitung untuk versi model aktif |
| `menghitung` | Sedang diproses (kunci per tanggal aktif) |
| `berhasil` | Hasil terakhir valid dan sesuai data saat ini |
| `kedaluwarsa` | Data masukan berubah sesudah hasil dihitung, hasil belum diperbarui |
| `gagal` | Percobaan hitung terakhir gagal (pesan galat tersimpan); hasil lama, bila ada, tetap |

### 8.2 Hitung ulang satu tanggal (`PrediksiService`)
1. Ambil kunci (lock) untuk tanggal itu; tolak bila sedang `menghitung`.
2. Pastikan data lengkap 31 kelurahan; bila tidak, hentikan dengan pesan jelas dan status tidak diubah menjadi berhasil.
3. Set status `menghitung`.
4. Susun 31 baris dari `data_harian` + `kelurahan`; kirim lewat `PredictorClient`.
5. **Validasi respons sebelum menyimpan apa pun:**
   - `model_versi` sama dengan `MODEL_VERSI_AKTIF` yang diharapkan;
   - jumlah hasil = 31, tanpa kode ganda, tanpa kode asing, tidak ada kode yang hilang (**dicocokkan dengan `kode_wilayah`**, bukan urutan);
   - `probabilitas` angka 0–1; `tingkat` salah satu dari `rendah|sedang|tinggi`;
   - `simulasi` dibaca dari respons.
6. Bila valid: dalam **satu transaksi database**, simpan/ganti 31 baris `prediksi` (upsert menurut kelurahan, tanggal, versi) beserta snapshot fitur, lalu set status `berhasil`, `dihitung_pada`, `is_simulasi`.
7. Bila tidak valid atau layanan gagal: set status `gagal` + `pesan_galat` (tanpa jejak teknis ke tampilan). **Hasil lama tidak dihapus atau diubah.**
8. Lepas kunci.

### 8.3 Aturan hasil lama
- Hasil lama **tidak pernah dihapus** sebelum hasil baru lolos validasi dan tersimpan.
- Hasil publik memakai snapshot fitur yang tersimpan bersama prediksi, sehingga panel detail selalu menampilkan fitur yang benar-benar dipakai model.
- Bila status `kedaluwarsa` atau `gagal` dan hasil lama masih ada, peta tetap menampilkannya dengan catatan "hasil belum diperbarui dengan data terbaru".
- Tanggal tersedia bagi publik = tanggal yang punya 31 baris prediksi valid untuk versi model aktif.
- Menghapus seluruh data satu tanggal (dengan konfirmasi) menghapus data harian, prediksi, dan status tanggal itu.

### 8.4 Penanda simulasi
- Kolom `is_simulasi` pada `status_prediksi` dan `prediksi`.
- Publik: banner "Hasil simulasi, bukan keluaran model ML" bila hasil simulasi; juga di Tentang Data.
- Admin: lencana "SIMULASI" di tabel tanggal dan dashboard.
- Hasil simulasi dan hasil model tidak boleh bercampur di satu versi: stub memakai versi `stub-v0`.
- Setelah model asli aktif dan prediksi dihitung ulang, hasil `stub-v0` tidak lagi tampil (versi aktif berbeda).

## 9. Antarmuka peta publik: status tampilan

| Keadaan | Tampilan |
|---|---|
| Awal (belum memilih tanggal) | Peta abu-abu, pesan "Pilih tanggal lalu tekan Cari" |
| Memuat | Indikator loading di atas peta, tombol Cari nonaktif, peta lama (bila ada) diredupkan |
| Kosong: tanggal tidak punya hasil | Pesan "Data untuk tanggal ini belum tersedia"; peta kembali abu-abu |
| Kosong: tidak ada tanggal tersedia sama sekali | Pesan "Belum ada data prediksi"; date picker nonaktif |
| Galat jaringan/server | Pesan ramah + tombol "Coba lagi"; peta lama tetap tampil |
| Layanan prediksi tidak tersedia (503) | Pesan "Prediksi sedang tidak tersedia"; hasil tersimpan tetap dapat dibuka |
| Galat memuat GeoJSON | Pesan khusus + tombol coba lagi; panel detail tetap bisa dipakai dari daftar |
| Hasil berhasil | Peta berwarna, legenda, versi model, waktu pembaruan (WIB) |
| Hasil simulasi | Banner simulasi tampil di atas peta |
| Hasil kedaluwarsa | Catatan "hasil belum diperbarui dengan data terbaru" |

Warna bukan satu-satunya penanda: legenda, tooltip, dan panel detail selalu menampilkan teks tingkat.

## 10. Rute dan API

### 10.1 Halaman
| Rute | Halaman |
|---|---|
| `GET /` | Beranda (opsional) |
| `GET /peta` | Dashboard peta |
| `GET /tentang` | Tentang Data |
| `GET/POST /admin/login`, `POST /admin/logout` | Login/logout admin |
| `GET /admin` | Dashboard admin |
| `GET /admin/data` | Daftar tanggal: kelengkapan + status prediksi |
| `GET /admin/data/{tanggal}` | Tabel 31 kelurahan + daftar kelurahan tanpa data |
| `PUT /admin/data/{tanggal}` | Simpan perubahan satu tanggal |
| `DELETE /admin/data/{tanggal}` | Hapus data satu tanggal |
| `POST /admin/data/{tanggal}/hitung` | Hitung ulang prediksi satu tanggal |
| `GET /admin/impor`, `GET /admin/impor/{id}` | Riwayat dan laporan impor |
| `GET /admin/aktivitas` | Log aktivitas |

Semua rute `/admin/*` (kecuali login) berada di balik middleware `auth`. Tidak ada rute registrasi.

### 10.2 Format respons JSON (rute `/data/*`)
Berhasil:
```json
{
  "status": "ok",
  "data": { },
  "meta": {
    "tanggal": "2026-09-30",
    "model_versi": "stub-v0",
    "simulasi": true,
    "jenis_data": "historis",
    "status_hasil": "berhasil",
    "diperbarui_pada": "2026-10-31T10:00:00+07:00"
  }
}
```
Galat:
```json
{ "status": "error", "kode": "TANGGAL_TIDAK_TERSEDIA", "pesan": "Data untuk tanggal ini belum tersedia.", "detail": {} }
```

| Kode HTTP | `kode` | Kapan |
|---|---|---|
| 200 | – | Berhasil |
| 404 | `TANGGAL_TIDAK_TERSEDIA` | Tanggal tidak punya hasil untuk versi aktif |
| 404 | `KELURAHAN_TIDAK_DITEMUKAN` | `kode_wilayah` tidak ada |
| 422 | `PARAMETER_TIDAK_VALID` | `tanggal` kosong, bukan format `Y-m-d`, atau bukan tanggal valid |
| 429 | `TERLALU_BANYAK_PERMINTAAN` | Melewati batas `throttle` |
| 503 | `LAYANAN_PREDIKSI_TIDAK_TERSEDIA` | Layanan mati dan hasil belum tersimpan (untuk aksi yang butuh layanan) |
| 500 | `GALAT_SERVER` | Galat tak terduga (pesan umum, tanpa detail teknis) |

### 10.3 Endpoint data
| Rute | Fungsi | Validasi |
|---|---|---|
| `GET /data/kelurahan` | Daftar 31 kelurahan | – |
| `GET /data/tanggal-tersedia` | Daftar tanggal yang punya hasil (urut naik) | – |
| `GET /data/prediksi?tanggal=` | Hasil seluruh kelurahan | `tanggal` wajib, `Y-m-d`, valid |
| `GET /data/prediksi/{kode_wilayah}?tanggal=` | Detail satu kelurahan | `kode_wilayah` ada di tabel; `tanggal` seperti di atas |
| `GET /data/ringkasan?tanggal=` | Jumlah kelurahan per tingkat | `tanggal` seperti di atas |

Contoh `data` pada `GET /data/prediksi`:
```json
[
  {
    "kode_wilayah": "…",
    "nama": "Pluit",
    "kecamatan": "Penjaringan",
    "tingkat": "tinggi",
    "probabilitas": 0.82,
    "fitur": { "elevasi": 0.4, "jarak_pantai": 0.35, "curah_hujan": 12.5,
               "pasang_tertinggi": 1.2, "gelombang_tertinggi": 0.9 }
  }
]
```
Peta mencocokkan hasil dengan poligon GeoJSON memakai `kode_wilayah`.

## 11. Data dan struktur proyek

### 11.1 Skema (migration Laravel)
**`kelurahan`** (31 baris, hampir tidak berubah): `id`, `kode_wilayah` (string, unik, format `NN.NN.NN.NNNN`), `nama` (diisi dari kolom `kelurahan` pada CSV), `kecamatan`, `elevasi` (m), `jarak_pantai` (km), `stasiun_pasut` (wajib; nilai `Sunda Kelapa` atau `Kolinlamil`).

**`data_harian`** (satu baris = satu kelurahan pada satu tanggal): `id`, `kelurahan_id` (FK), `tanggal`, `curah_hujan`, `pasang_tertinggi`, `gelombang_tertinggi`, `sumber` (`manual` | `impor_csv` | `seed`), `impor_id` (FK nullable), timestamps; unik (`kelurahan_id`, `tanggal`).

**`status_prediksi`** (satu baris = satu tanggal pada satu versi model): `id`, `tanggal`, `model_versi`, `status` (lihat 8.1), `is_simulasi`, `jumlah_hasil`, `dihitung_pada` (nullable), `pesan_galat` (nullable), timestamps; unik (`tanggal`, `model_versi`).

**`prediksi`**: `id`, `kelurahan_id` (FK), `tanggal`, `model_versi`, `probabilitas` (0–1), `tingkat` (`rendah|sedang|tinggi`), `fitur_json` (snapshot fitur yang dipakai), `is_simulasi`, timestamps; unik (`kelurahan_id`, `tanggal`, `model_versi`).

**`impor`** (riwayat impor): `id`, `nama_berkas`, `jenis` (mis. `data_harian`), `sumber` (keterangan asal data, mis. "BMKG", "hasil olah tim"), `admin_id` (nullable; kosong bila lewat artisan), `status` (`berhasil` | `gagal`), `jumlah_baris`, `jumlah_berhasil`, `jumlah_galat`, `ringkasan_galat` (JSON, daftar baris + pesan, dibatasi mis. 200 entri), `dimulai_pada`, `selesai_pada`.

**`users`**: tabel bawaan Laravel; hanya admin.

**`log_aktivitas`**: `admin_id`, `aksi` (create/update/delete/hitung/impor/login/logout), `tabel`, `ref_id`, `detail`, `waktu`.

Catatan:
- Kelengkapan data **tidak** disimpan sebagai kolom; selalu dihitung dari `data_harian` agar tidak bisa tidak sinkron.
- Batas poligon disimpan sebagai berkas `public/data/kelurahan.geojson`, bukan di database.
- Dataset latih tidak ada di database ini (bagian 4).

### 11.2 Format CSV
`data_harian2026.csv` (satu baris per kelurahan per tanggal; pemisah `,` atau `;`; desimal titik; UTF-8; tanggal ISO):
```
kode_wilayah,tanggal,curah_hujan,pasang_tertinggi,gelombang_tertinggi
NN.NN.NN.NNNN,2026-01-01,0.0,1.05,0.8
```
`kelurahan.csv` (untuk seeder): `kode_wilayah,kelurahan,kecamatan,elevasi,jarak_pantai,stasiun_pasut`. Seeder memetakan kolom `kelurahan` ke kolom database `nama`, menolak berkas bila barisnya bukan 31, kode ganda atau berformat salah, `stasiun_pasut` di luar dua nilai yang diizinkan, atau `jarak_pantai` tidak berupa angka.

`data_latih.csv` tidak diimpor ke website (hanya untuk ML).

Kunci kelurahan selalu `kode_wilayah`, bukan nama dan bukan kode pos. Detail struktur lengkap ada di 5.7.

### 11.3 Cara memasukkan data
1. **Kelurahan (sekali di awal):** letakkan `data/kelurahan.csv`, jalankan `php artisan db:seed --class=KelurahanSeeder`.
2. **Validasi GeoJSON:** `php artisan geojson:validasi` (detail di 11.4).
3. **Data harian:** `php artisan import:data-harian data/data_harian2026.csv --sumber="…" [--prediksi]`. Semua atau tidak sama sekali (transaksi). Hasilnya dicatat di tabel `impor` dan terlihat di halaman Impor; ringkasan juga dicetak di terminal.
4. **Koreksi kecil:** halaman admin per tanggal.
5. **Prediksi massal / setelah ganti model:** `php artisan prediksi:hitung --dari=2026-01-01 --sampai=2026-10-31`.
6. **Admin:** `php artisan admin:buat`.

### 11.4 Validasi GeoJSON (Must)
Perintah `php artisan geojson:validasi` dan sebuah tes otomatis memeriksa:
- berkas ada, JSON valid, bertipe `FeatureCollection`;
- tepat **31** fitur;
- setiap fitur punya `properties.kode_wilayah`;
- tidak ada kode ganda;
- himpunan kode **sama persis** dengan kolom `kode_wilayah` di tabel `kelurahan` (lapor kode yang kelebihan dan yang kurang);
- geometri bertipe `Polygon` atau `MultiPolygon`.
Dashboard admin menampilkan hasil pemeriksaan terakhir. Peta publik menolak memuat GeoJSON yang gagal validasi dasar (tampilan galat GeoJSON, bagian 9).

### 11.5 Struktur folder
```
C:\laragon\www\rob-predictor\
├─ prd.md
├─ AGENTS.md                     # instruksi ringkas untuk agen AI (bagian 20)
├─ laravel-app\
│  ├─ app\
│  │  ├─ Console\Commands\       # ImportDataHarian, HitungPrediksi, BuatAdmin, ValidasiGeojson
│  │  ├─ Http\Controllers\       # Peta, Data, Tentang; Admin\(Dashboard, DataHarian, Impor, Aktivitas)
│  │  ├─ Http\Requests\          # validasi form dan parameter
│  │  ├─ Models\                 # Kelurahan, DataHarian, Prediksi, StatusPrediksi, Impor, LogAktivitas
│  │  └─ Services\               # PredictorClient, PrediksiService, ImporService
│  ├─ database\{migrations,seeders}\ , database.sqlite
│  ├─ resources\{views,js}\
│  ├─ routes\web.php
│  ├─ public\data\kelurahan.geojson
│  └─ tests\{Feature,Unit}\
├─ ml-service\
│  ├─ app.py                     # FastAPI: /health, /predict
│  ├─ model_artifacts\
│  ├─ tests\                     # pytest
│  └─ requirements.txt
├─ ml\                           # notebook, train.py, cek_dataset.py (tim ML)
├─ data\
│  ├─ kelurahan.csv
│  ├─ data_latih.csv             # hanya untuk ML
│  ├─ data_harian2026.csv        # diimpor ke website
│  └─ catatan_dataset.md         # kamus data
└─ jalankan-ml-service.bat
```

### 11.6 Variabel `.env` penting
```
APP_URL=http://rob-predictor.test
DB_CONNECTION=sqlite
PREDICTOR_URL=http://127.0.0.1:8001
PREDICTOR_TOKEN=<rahasia-acak>
PREDICTOR_TIMEOUT=10
MODEL_VERSI_AKTIF=stub-v0
```

## 12. Halaman Tentang Data (Should)

Isi wajib, sebagian diisi tim ML saat model asli siap:
1. **Jenis data:** hasil yang tampil adalah prediksi atas data historis (tahun 2026), bukan prakiraan masa depan. Bila `simulasi`, nyatakan jelas bahwa hasil belum berasal dari model ML.
2. **Sumber data:** tabel fitur → sumber → resolusi/cakupan → periode → catatan (mis. pasang dari prediksi pasut BIG untuk dua stasiun, Sunda Kelapa dan Kolinlamil; gelombang; hujan; elevasi; jarak pantai). Diisi tim, bersumber dari `data/catatan_dataset.md`.
3. **Metode:** jenis model, cara membagi data latih/uji, metrik uji, cara tingkat diturunkan dari probabilitas. Sebelum model asli ada, tampilkan "model belum tersedia".
4. **Keterbatasan:** data 2026 tidak berlabel sehingga akurasinya tidak bisa dihitung; nilai fitur harian dapat sama di banyak kelurahan; label kejadian berasal dari laporan (bias pelaporan); prediksi bukan peringatan resmi.
5. **Disclaimer:** "Hasil ini adalah prediksi model atas data historis untuk keperluan akademik, bukan peringatan resmi. Rujuk BMKG/BPBD untuk informasi resmi."
6. **Atribusi** sumber data sesuai lisensi dan **versi model** serta tanggal pembaruan terakhir.

## 13. Aturan validasi (data harian)

- `kode_wilayah` berformat `NN.NN.NN.NNNN` (teks) **dan** ada di tabel `kelurahan`.
- `tanggal` berformat `YYYY-MM-DD` dan valid. Format `MM-DD-YYYY` atau `DD/MM/YYYY` ditolak dengan pesan "gunakan format YYYY-MM-DD" (ambigu, mudah tertukar hari dan bulan).
- Kombinasi (`kode_wilayah`, `tanggal`) unik.
- `curah_hujan` ≥ 0 (mm/hari); `gelombang_tertinggi` ≥ 0 (m); `pasang_tertinggi` (m) dalam rentang wajar yang disepakati dengan tim ML.
- Semua nilai wajib terisi dan berupa angka (desimal titik; berkoma ditolak).
- Satuan tetap; tampilkan satuan di label input.
- Impor: kolom wajib ada dan bernama persis; semua atau tidak sama sekali; laporan galat per baris disimpan di `impor`.
- Kelengkapan satu tanggal = 31 kelurahan punya data.

## 14. Keamanan

- Kata sandi di-hash (bawaan Laravel). Tidak ada rute registrasi.
- Alamat `/admin/login` yang tidak ditautkan hanyalah kenyamanan, **bukan pengaman**. Pengamannya: middleware `auth` di server, `throttle` pada login, sesi dan CSRF bawaan Laravel.
- Layanan Python hanya di `127.0.0.1` dan menuntut `X-Service-Token`.
- `.env` dan `database.sqlite` masuk `.gitignore`.
- `APP_DEBUG=false` di luar pengembangan; pesan galat ke pengguna tidak membuka detail teknis.
- Eloquent/query builder, tanpa SQL mentah yang menyisipkan input pengguna.
- `throttle` pada rute `/data/*`.
- Tidak ada data pribadi pengunjung yang disimpan.

## 15. Persyaratan non-fungsional

- `GET /data/prediksi` < 1 detik (hasil sudah tersimpan); hitung ulang satu tanggal < 5 detik.
- Responsif (laptop dan ponsel); antarmuka berbahasa Indonesia.
- Aksesibilitas: legenda berteks, kontras cukup, tidak hanya mengandalkan warna.
- Waktu ditampilkan dalam WIB.
- Cadangan berkala `database.sqlite` (salin berkas) sebelum perubahan besar.

## 16. Pengujian (Must)

Otomatis (PHPUnit atau Pest untuk Laravel; pytest untuk layanan Python). Layanan Python dipalsukan dengan `Http::fake()` pada tes Laravel.

**Publik / API**
- `/data/kelurahan` mengembalikan 31 kelurahan.
- `/data/tanggal-tersedia` hanya memuat tanggal yang punya 31 hasil untuk versi aktif.
- `/data/prediksi` benar untuk tanggal valid; `422` untuk format salah/kosong; `404` untuk tanggal tanpa hasil; `KELURAHAN_TIDAK_DITEMUKAN` untuk kode asing.
- Bentuk respons sesuai 10.2 (termasuk `meta.simulasi` dan `meta.model_versi`).

**Data dan impor**
- Impor CSV valid berhasil dan tercatat di `impor`.
- Impor dengan satu baris salah: tidak ada baris tersimpan, laporan galat tercatat.
- Desimal berkoma ditolak; pemisah `;` diterima.
- Tanggal `06-01-2023` (bukan ISO) ditolak; `2026-01-06` diterima.
- `kode_wilayah` berformat salah atau tidak ada di tabel `kelurahan` ditolak.
- Seeder `kelurahan.csv`: menolak berkas bukan 31 baris, kode ganda, `stasiun_pasut` di luar `Sunda Kelapa`/`Kolinlamil`, atau `jarak_pantai` non-angka.
- CRUD data harian per tanggal: validasi menolak nilai negatif, kode asing, dan duplikat.
- Daftar kelurahan tanpa data benar; kelengkapan n/31 benar.

**Prediksi**
- Hitung ulang sukses menyimpan 31 baris dan status `berhasil`.
- Respons dengan kode ganda/hilang/asing atau jumlah ≠ 31 ditolak; status `gagal`; **hasil lama tetap utuh**.
- Respons dengan urutan berbeda tetap dicocokkan benar lewat `kode_wilayah`.
- Layanan Python mati: data tetap tersimpan, status `kedaluwarsa`/`belum_dihitung`, halaman publik tetap bekerja.
- Versi model di respons tidak sama dengan `MODEL_VERSI_AKTIF`: ditolak.
- Hasil stub ditandai `is_simulasi` dan memunculkan banner.
- Hitung ulang per tanggal menolak tanggal yang datanya tidak lengkap.

**Admin dan keamanan**
- Rute admin menolak akses tanpa login; tidak ada rute registrasi.
- Login/logout berfungsi; tombol "Admin" hanya tampil setelah login.

**GeoJSON**
- Tes validasi 31 kode cocok dengan tabel `kelurahan` (gagal bila ada kode kurang/lebih/ganda).

**Layanan Python (pytest)**
- `/health` memuat mode, versi, dan daftar fitur; `/predict` menolak token salah (`401`) dan masukan tidak valid (`422`); hasil membawa `kode_wilayah`.

**Manual (daftar periksa)**
- Seluruh alur publik dan admin, termasuk status loading/kosong/galat di peta.
- Tampilan laptop dan ponsel.
- Mematikan layanan Python saat demo: situs tetap bekerja dengan pesan yang tepat.

## 17. Tahapan pengembangan

### Tahap 1 — Persiapan proyek (mulai di sini)
- [ ] Siapkan Laragon, PHP (ekstensi SQLite aktif), Composer, Node; buat proyek Laravel dan atur `.env` (SQLite).
- [ ] Buat struktur folder proyek (11.5).
- [ ] Siapkan 31 data kelurahan (`kelurahan.csv`) dengan kode wilayah resmi Kemendagri (teks, O6), `jarak_pantai` dalam km, dan `stasiun_pasut` yang valid.
- [ ] Siapkan `data/catatan_dataset.md` (kerangka di 5.7) dan `AGENTS.md` (bagian 20).
- [ ] Siapkan `kelurahan.geojson`.
- [ ] Buat migration (termasuk `status_prediksi`, `impor`) dan seeder.
- [ ] Buat `geojson:validasi` dan pastikan lolos 31 kode.

### Tahap 2 — Backend dan data
- [ ] Model Eloquent.
- [ ] CRUD data harian (per tanggal) dengan validasi (bagian 13).
- [ ] Sistem impor CSV lewat Artisan, mencatat riwayat dan laporan di tabel `impor`.
- [ ] Perhitungan kelengkapan data dan daftar kelurahan tanpa data.
- [ ] API: `/data/kelurahan`, `/data/tanggal-tersedia`, format respons dan kode galat baku (10.2).
- [ ] Tes untuk bagian ini.

### Tahap 3 — Layanan prediksi tiruan
- [ ] FastAPI dengan `/health` dan `/predict`.
- [ ] Mode stub (`stub-v0`) dengan penanda `simulasi`.
- [ ] `PredictorClient` dan `PrediksiService` (validasi respons berdasarkan `kode_wilayah`, transaksi, hasil lama tidak dihapus).
- [ ] Status prediksi (8.1) dan perintah `prediksi:hitung`.
- [ ] Uji penyimpanan hasil, kegagalan layanan, dan respons tidak valid.
- [ ] `/data/prediksi`, `/data/prediksi/{kode}`, `/data/ringkasan`.

### Tahap 4 — Website publik
- [ ] Halaman peta dengan Leaflet + GeoJSON.
- [ ] Date picker (hanya tanggal tersedia) dan tombol Cari.
- [ ] Warna risiko, legenda berteks, panel detail kelurahan.
- [ ] Status tampilan: loading, kosong, galat (bagian 9).
- [ ] Versi model, waktu pembaruan, banner simulasi.
- [ ] Halaman Tentang Data (bagian 12).

### Tahap 5 — Website admin
- [ ] Login admin tanpa registrasi; tombol "Admin" dengan menu dan logout.
- [ ] Dashboard admin (kelengkapan, status prediksi, model aktif, kesehatan layanan, hasil validasi GeoJSON, impor terakhir).
- [ ] Halaman daftar tanggal dan halaman per tanggal; daftar kelurahan tanpa data.
- [ ] Status kelengkapan data dan status prediksi (terpisah) per tanggal.
- [ ] Hitung ulang per tanggal.
- [ ] Halaman riwayat dan laporan impor.
- [ ] Riwayat aktivitas.

### Tahap 6 — Pengujian dan penyelesaian
- [ ] Jalankan seluruh tes otomatis (bagian 16) dan daftar periksa manual.
- [ ] Uji ketika layanan Python mati.
- [ ] Uji validasi data dan impor CSV.
- [ ] Uji tampilan laptop dan ponsel.
- [ ] README dan panduan menjalankan proyek di Laragon (termasuk `jalankan-ml-service.bat`).

### Tahap 7 — Integrasi Machine Learning asli (menyusul)
Dikerjakan setelah dataset dan model tim ML siap.
- [ ] Ganti stub dengan model asli (`model_vN.joblib` + `.json`); ubah `MODEL_VERSI_AKTIF`.
- [ ] Jalankan `ml/cek_dataset.py` pada `data_latih.csv`; pastikan urutan dan satuan fitur sesuai kontrak (5.1, `jarak_pantai` dalam km); cocokkan daftar fitur lewat `/health`.
- [ ] Uji hasil API terhadap hasil notebook pada beberapa baris.
- [ ] Hitung ulang prediksi dengan versi model baru (`prediksi:hitung`).
- [ ] Perbarui Tentang Data (sumber, metode, metrik) dan pastikan banner simulasi hilang.

## 18. Kriteria siap demonstrasi

Semua butir **Must** pada lampiran A selesai, dan:
- Pengunjung dapat memilih tanggal tersedia, menekan Cari, dan melihat 31 kelurahan berwarna; klik kelurahan menampilkan detail.
- Status loading, kosong, dan galat tampil benar.
- Hasil stub tampak jelas sebagai simulasi di peta dan admin.
- Rute admin menolak akses tanpa login; tidak ada registrasi; logout lewat tombol "Admin" berfungsi.
- Admin dapat melihat kelurahan tanpa data, status prediksi per tanggal, dan menghitung ulang per tanggal.
- Hasil lama tetap ada bila hitung ulang gagal.
- Validasi GeoJSON 31 kode lolos; tes otomatis lulus.
- Situs tetap bekerja ketika layanan Python mati.
- Tidak ada layanan berbayar; tidak ada fitur unduh publik.

## 19. Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Hasil stub dikira hasil model asli | Penilai/pengguna tertipu | Penanda simulasi di database, API, peta, admin (D14); versi `stub-v0` terpisah |
| Dua program (Laravel + Python) harus berjalan saat demo | Demo gagal bila salah satu mati | `jalankan-ml-service.bat`; situs tetap jalan tanpa layanan; cadangan opsi B |
| Daftar fitur berubah saat riset ML | Perlu migration dan perubahan impor | Kontrak fitur di `model_vN.json`; cek lewat `/health`; fitur dibatasi ke lima sampai model final |
| Hasil API tertukar antar kelurahan | Peta salah tanpa galat | Pencocokan dengan `kode_wilayah`, validasi kelengkapan kode, tes urutan berbeda |
| Hitung ulang gagal di tengah jalan | Hasil publik hilang/rusak | Validasi sebelum simpan, transaksi, hasil lama dipertahankan |
| GeoJSON tidak cocok dengan kode kelurahan | Sebagian kelurahan tidak berwarna | `geojson:validasi` + tes otomatis |
| CSV Excel berdesimal koma | Angka salah terbaca | Tolak desimal koma dengan pesan jelas; template CSV |
| Versi PHP Laragon tidak memenuhi syarat Laravel | Proyek gagal berjalan | Cek syarat Laravel sebelum membuat proyek; ganti versi PHP di Laragon |
| Data 2026 tanpa label | Akurasi 2026 tidak bisa dihitung | Uji di 2025 (pembagian waktu); nyatakan di Tentang Data |
| Nilai fitur harian hampir sama antar kelurahan | Peta cenderung "naik-turun bersama" | Jelaskan di Tentang Data; analisis variasi fitur oleh tim ML |
| Persistensi SQLite bila di-hosting | Data hilang | Demo lokal; cadangkan `database.sqlite` |
| Tanggal MM-DD-YYYY tertukar dengan DD-MM-YYYY | Fitur dan label tidak sejajar, hasil salah tanpa galat | Wajib ISO; pemeriksa menolak format lain; cek tanggal dengan hari > 12 |
| Baris 0 di `data_latih.csv` hanya sebagian | Probabilitas bias, model tampak lebih baik dari kenyataan | Buat dari semua kelurahan × semua tanggal (O7); catat bila tidak |
| Dataset manual tanpa skrip pembuat | Koreksi satu kesalahan harus diulang di banyak tempat | `cek_dataset.py` + `catatan_dataset.md`; simpan salinan berkas mentah |
| Laporan tingkat kecamatan disebar ke semua kelurahan | Label 1 membengkak | Hanya pakai laporan tingkat kelurahan atau tandai terpisah |
| Kode wilayah terbaca sebagai angka atau salah sumber | Gabungan antar berkas gagal | Simpan sebagai teks; satu sumber resmi (O6); validasi format |
| Kelurahan tanpa contoh di `data_latih.csv` | Model tidak punya dasar untuk wilayah itu | Pemeriksaan "31 kelurahan muncul" di `cek_dataset.py` |

## 20. Dokumen pendukung dan instruksi untuk agen AI

Agen AI hanya mengetahui isi berkas yang diberikan kepadanya atau yang ditunjuk secara eksplisit. Berkas di folder tidak otomatis dibaca. Karena itu dokumen pendukung didaftarkan di sini dan di `AGENTS.md`.

| Dokumen | Fungsi | Kapan dibaca |
|---|---|---|
| `prd.md` | Spesifikasi sistem | Selalu, sebelum mengerjakan tugas apa pun |
| `data/catatan_dataset.md` | Kamus data: sumber, satuan, aturan label, pembuatan baris 0, keterbatasan | Tugas yang menyentuh dataset, model, impor data, atau halaman Tentang Data |
| `AGENTS.md` | Instruksi ringkas agen (nama berkas bisa berbeda menurut alat coding yang dipakai) | Dimuat otomatis oleh alat yang mendukungnya; bila tidak, sebutkan jalurnya di prompt |
| `README.md` | Cara menjalankan proyek di Laragon | Saat menyiapkan atau menjalankan lingkungan |

Isi awal `AGENTS.md`:
```
# Instruksi untuk agen
- Baca prd.md sebelum mengerjakan apa pun. Kerjakan hanya tahap yang diminta.
- Stack: Laravel + SQLite (Laragon, Windows); layanan prediksi Python/FastAPI di 127.0.0.1:8001.
- Kunci kelurahan selalu kode_wilayah (teks, format NN.NN.NN.NNNN), bukan nama dan bukan kode pos.
- Tanggal selalu YYYY-MM-DD; desimal titik; CSV UTF-8. jarak_pantai dalam km.
- Hasil prediksi dicocokkan dengan kode_wilayah, bukan urutan.
- Jangan hapus hasil prediksi lama sebelum hasil baru valid dan tersimpan.
- Hasil stub harus selalu ditandai simulasi.
- Untuk tugas yang menyentuh dataset, model, atau halaman Tentang Data, baca data/catatan_dataset.md dan prd.md bagian 5.
- Dataset latih (data/data_latih.csv) tidak diimpor ke database website.
- Jangan commit .env, database.sqlite, atau data mentah berlisensi.
- Tanpa layanan berbayar, tanpa fitur unduh publik, tanpa registrasi pengguna.
- Jalankan tes sebelum menyatakan tugas selesai.
```

Saat memberi tugas ke agen, sebutkan tahap dan jalur berkas yang relevan (mis. "Kerjakan Tahap 2; baca prd.md bagian 11 dan 13").

## Lampiran A. Pelacakan 15 perubahan

| No | Bagian | Perubahan | Prioritas | Lokasi di PRD |
|---|---|---|---|---|
| 1 | Database | Pisahkan kelengkapan data dan status prediksi | Must | 8.1, 11.1 |
| 2 | Database | Catat sumber dan riwayat impor | Should | 11.1 (`impor`, `sumber`), A11 |
| 3 | Admin | Tampilkan kelurahan yang belum memiliki data | Must | A5, 7.2 |
| 4 | Admin | Tampilkan status prediksi per tanggal | Must | A3, 7.2 |
| 5 | Admin | Hitung ulang per tanggal | Must | A6, 8.2, 10.1 |
| 6 | Admin | Laporan hasil impor | Should | A11, 11.1, 11.3 |
| 7 | Peta publik | Loading, empty state, error state | Must | P5, bagian 9 |
| 8 | Peta publik | Versi model dan waktu pembaruan | Should | P7, 10.2 |
| 9 | GeoJSON | Validasi kecocokan 31 kode wilayah | Must | 11.4 |
| 10 | API | Format respons, status, dan validasi | Must | 10.2, 10.3 |
| 11 | API | Cocokkan hasil dengan kode wilayah | Must | 5.3, 8.2, 10.3 |
| 12 | Prediksi | Jangan hapus hasil lama sebelum hasil baru valid | Must | 8.2, 8.3 |
| 13 | Pengujian | Uji seluruh alur utama | Must | Bagian 16 |
| 14 | Tentang Data | Perjelas sumber, metode, keterbatasan, jenis data | Should | Bagian 12 |
| 15 | Mode stub | Tampilkan bahwa hasil masih simulasi | Must | D14, 8.4, P6, A12 |

## Lampiran B. Riwayat perubahan v0.4

| Perubahan | Lokasi |
|---|---|
| Nama berkas disesuaikan: `data_latih.csv`, `data_harian2026.csv`, `kelurahan.csv` | D7, 5.7, 11.2, 11.3, 11.5 |
| `jarak_pantai` ditetapkan km (sebelumnya "m atau km") | 5.1, 5.7, 11.1 |
| `stasiun_pasut` wajib, dua nilai (Sunda Kelapa, Kolinlamil) | D17, 11.1, 11.2 |
| Label bernama `is_banjir`; tidak dikirim ke layanan prediksi | 5.1, 5.7 |
| `kode_wilayah` Kemendagri sebagai teks `NN.NN.NN.NNNN`; bukan kode pos | D15, O6, 13 |
| Tanggal ISO wajib; `MM-DD-YYYY` ditolak | D16, 13 |
| Acuan dataset untuk tim ML (aturan label, baris 0, pembagian data, pemeriksa) | 5.7, O7 |
| Jumlah RT tergenang tidak dicatat; unit kelurahan | O5, 5.7 |
| `catatan_dataset.md` dan instruksi agen AI (`AGENTS.md`) | D18, bagian 20 |
| Tes dan risiko baru terkait format data | Bagian 16, 19 |
