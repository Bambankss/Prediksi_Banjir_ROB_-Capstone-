# Instruksi untuk agen

Proyek: **Website Prediksi Banjir ROB Jakarta Utara** (Capstone Machine Learning, tanpa anggaran).
Spesifikasi lengkap ada di `prd.md` (v0.4). Berkas ini hanya ringkasan aturan yang paling sering dilanggar. Bila ada perbedaan, `prd.md` yang berlaku.

## Cara bekerja

- Baca `prd.md` sebelum mengerjakan apa pun. Kerjakan **hanya tahap yang diminta** (Tahap 1-7, bagian 17 PRD).
- Untuk tugas yang menyentuh dataset, model, impor data, atau halaman Tentang Data, baca juga `data/catatan_dataset.md` dan `prd.md` bagian 5.
- Jalankan tes sebelum menyatakan tugas selesai (Laravel: PHPUnit/Pest; layanan Python: pytest).
- Bila ada hal yang tidak jelas atau bertentangan dengan PRD, tanyakan; jangan menebak. Khusus kode wilayah, jangan ambil dari ingatan (lihat O6).

## Stack dan lingkungan

- Laravel + **SQLite** (bukan MySQL), dikembangkan di **Laragon (Windows)**. Contoh `APP_URL=http://rob-predictor.test`.
- Layanan prediksi: Python/FastAPI di `127.0.0.1:8001`, stateless, **tanpa akses database**. Hanya Laravel yang menulis ke SQLite.
- Setiap permintaan ke layanan Python membawa header `X-Service-Token` (dari `.env`).
- Peta: Leaflet + GeoJSON (`laravel-app/public/data/kelurahan.geojson`).
- Perintah `php artisan ...` dijalankan dari terminal Laragon di folder `laravel-app`.

## Aturan data (jangan dilanggar)

- Kunci kelurahan selalu `kode_wilayah`: **teks**, format `NN.NN.NN.NNNN` (kode Kemendagri). Bukan nama, bukan kode pos, bukan angka. Di pandas pakai `dtype=str`.
- Tanggal selalu `YYYY-MM-DD`. Format `MM-DD-YYYY` dan `DD/MM/YYYY` ditolak.
- Desimal memakai titik (nilai berkoma ditolak). CSV berencoding UTF-8; pemisah `,` (impor juga menerima `;`).
- Satuan: `elevasi` m, `jarak_pantai` **km**, `curah_hujan` mm/hari, `pasang_tertinggi` m, `gelombang_tertinggi` m.
- `stasiun_pasut` hanya `Sunda Kelapa` atau `Kolinlamil`.
- Urutan fitur model: `elevasi, jarak_pantai, curah_hujan, pasang_tertinggi, gelombang_tertinggi`.
- Label `is_banjir` (0/1) **tidak pernah** dikirim ke layanan prediksi dan tidak ada di `data_harian2026.csv`.
- Satu tanggal lengkap = 31 kelurahan punya data.
- Dataset latih (`data/data_latih.csv`) **tidak diimpor** ke database website.
- Kelengkapan data tidak disimpan sebagai kolom; selalu dihitung dari `data_harian`.

## Aturan prediksi

- Hasil prediksi dicocokkan dengan `kode_wilayah`, **bukan urutan**.
- Validasi respons sebelum menyimpan: `model_versi` sama dengan `MODEL_VERSI_AKTIF`, tepat 31 hasil, tanpa kode ganda/asing/hilang, `probabilitas` 0-1, `tingkat` salah satu dari `rendah|sedang|tinggi`.
- Simpan hasil dalam **satu transaksi**. Jangan hapus hasil prediksi lama sebelum hasil baru valid dan tersimpan; bila hitung ulang gagal, hasil lama tetap.
- Status prediksi terpisah dari kelengkapan data: `belum_dihitung`, `menghitung`, `berhasil`, `kedaluwarsa`, `gagal`.
- Hasil stub (`stub-v0`) **selalu ditandai simulasi** di database (`is_simulasi`), API (`meta.simulasi`), dan antarmuka (banner/lencana). Hasil stub dan hasil model tidak boleh bercampur dalam satu versi.
- Situs publik harus tetap bekerja ketika layanan Python mati.

## Aturan produk

- Dashboard publik tanpa login; login hanya untuk admin lewat `/admin/login` (tidak ditautkan di navigasi publik; itu bukan pengaman, pengamannya middleware `auth`, `throttle`, CSRF).
- Tidak ada registrasi pengguna.
- Tidak ada fitur unduh hasil publik.
- Tanpa layanan berbayar.
- Admin hanya mengelola data masukan yang diprediksi, bukan dataset latih atau label.
- Antarmuka berbahasa Indonesia; waktu ditampilkan dalam WIB; warna bukan satu-satunya penanda (selalu ada teks tingkat).

## Keamanan dan repositori

- Jangan commit `.env`, `database.sqlite`, atau data mentah berlisensi.
- Gunakan Eloquent/query builder; jangan menyisipkan input pengguna ke SQL mentah.
- `APP_DEBUG=false` di luar pengembangan; pesan galat ke pengguna tanpa detail teknis.

## Dokumen pendukung

| Dokumen | Fungsi | Kapan dibaca |
|---|---|---|
| `prd.md` | Spesifikasi sistem | Selalu, sebelum tugas apa pun |
| `data/catatan_dataset.md` | Kamus data: sumber, satuan, aturan label, pembuatan baris 0, keterbatasan | Tugas yang menyentuh dataset, model, impor data, atau halaman Tentang Data |
| `README.md` | Cara menjalankan proyek di Laragon | Saat menyiapkan atau menjalankan lingkungan |

## Peta bagian PRD

| Topik | Bagian |
|---|---|
| Keputusan dan pertanyaan terbuka | 2 |
| Lingkungan Laragon | 3 |
| Pemisahan data operasional dan dataset latih | 4 |
| Kontrak fitur, layanan FastAPI, format dataset | 5 |
| Status prediksi dan alur hitung ulang | 8 |
| Rute, format respons JSON, kode galat | 10 |
| Skema database, format CSV, struktur folder, `.env` | 11 |
| Aturan validasi | 13 |
| Pengujian | 16 |
| Tahapan pengembangan | 17 |

## Cara memberi tugas

Sebutkan tahap dan jalur berkas yang relevan, mis. "Kerjakan Tahap 2; baca `prd.md` bagian 11 dan 13".
