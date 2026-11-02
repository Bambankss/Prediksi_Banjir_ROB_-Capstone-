# UI Design Specification — Website Prediksi Banjir ROB Jakarta Utara

**Versi dokumen:** 1.1  
**Acuan utama:** `prd.md` versi 0.4  
**Status:** Spesifikasi UI/UX untuk implementasi Laravel + SQLite  
**Lingkungan:** Laragon (Windows)  
**Bahasa antarmuka:** Indonesia  
**Target:** Desktop/laptop, tablet, dan ponsel

> **Aturan utama:** `prd.md` v0.4 tetap menjadi sumber kebenaran untuk ruang lingkup, prioritas, aturan bisnis, rute, data, validasi, API, dan perilaku sistem. Dokumen ini hanya menjelaskan bagaimana kebutuhan tersebut ditampilkan dan digunakan melalui antarmuka. Jika terdapat perbedaan, PRD harus diikuti. Dokumen ini tidak menetapkan ulang ambang probabilitas, metode ML, atau aturan bisnis.

---

## 1. Tujuan dan prinsip desain

Dokumen ini menerjemahkan kebutuhan fungsional dan non-fungsional pada `prd.md` v0.4 menjadi panduan UI (User Interface, tampilan antarmuka) dan UX (User Experience, pengalaman pengguna) untuk Blade, CSS, JavaScript, dan komponen antarmuka.

Prinsip desain:

1. **Peta adalah pusat pengalaman publik.** Pengunjung memilih tanggal dan memahami hasil tingkat ROB per kelurahan.
2. **Informasi tidak boleh menyesatkan.** Hasil historis, simulasi, hasil kedaluwarsa, dan keterbatasan model harus dijelaskan secara eksplisit.
3. **Warna bukan satu-satunya penanda.** Tingkat risiko selalu disertai teks dan legenda.
4. **Pertahankan identitas visual rancangan awal.** Gunakan tema navy/biru gelap, aksen biru terang, kartu membulat, border halus, dan gradasi biru secara terukur.
5. **Peta tetap dominan.** Statistik dan kartu informasi menjadi pendukung, bukan pengganti peta.
6. **Admin berorientasi tugas.** Kelengkapan data, status prediksi, kelurahan yang belum memiliki data, dan tindakan hitung ulang mudah ditemukan.
7. **Responsif dan aksesibel.** Fungsi utama dapat digunakan dengan mouse, keyboard, dan layar kecil.
8. **Tidak mengarang data.** Angka, status, tanggal, versi model, dan sumber harus berasal dari API, database, atau dokumentasi yang benar.

### 1.1 Batas kewenangan dokumen

UI boleh menentukan tata letak, hierarki visual, label yang mudah dipahami, dan cara memberi umpan balik. UI tidak boleh:
- Menentukan sendiri nilai ambang (threshold, batas nilai untuk mengelompokkan probabilitas) Rendah, Sedang, dan Tinggi.
- Mengubah keluaran atau aturan model Machine Learning.
- Mengubah aturan validasi, format data, satuan, atau kontrak API.
- Menambahkan fitur di luar ruang lingkup PRD seolah-olah fitur tersebut wajib.
- Menampilkan data contoh sebagai data aktual.
- Mengubah prediksi historis menjadi klaim prakiraan masa depan atau peringatan resmi.

---

## 2. Interpretasi gambar referensi

Gambar referensi memperlihatkan **delapan rancangan layar**:
1. Login.
2. Dashboard/beranda publik.
3. Peta kerentanan dengan panel detail wilayah.
4. Dialog unduh data.
5. Dashboard admin.
6. Pengelolaan/pengunggahan dataset.
7. Halaman model.
8. Hasil prediksi.

Gambar adalah **referensi visual**, bukan spesifikasi fungsi.

### 2.1 Elemen visual yang dipertahankan

- Tema biru tua/navy dengan aksen biru terang.
- Latar gradasi biru gelap dan pencahayaan lembut pada area tertentu.
- Identitas/nama proyek sederhana di kiri atas.
- Navigasi horizontal pada layar lebar.
- Kartu informasi bersudut membulat dan border biru tipis.
- Peta sebagai elemen visual dominan.
- Panel informasi wilayah di samping peta pada layar lebar.
- Form login sederhana dan terpusat.
- Kartu ringkasan dengan angka menonjol selama datanya berasal dari sistem.

### 2.2 Elemen referensi yang tidak diikuti apa adanya

| Elemen rancangan lama | Keputusan berdasarkan PRD v0.4 |
|---|---|
| Tombol/halaman unduh publik | Tidak dibuat. PRD tidak menyediakan unduhan publik CSV/PNG/PDF. |
| Tombol login di navigasi publik | Tidak ditampilkan. Login admin hanya melalui `/admin/login` dan tidak ditautkan dari navigasi publik. |
| Dashboard statistik sebagai pusat halaman publik | Boleh menjadi landing page `/` opsional (Could). Fungsi utama publik tetap `/peta`. |
| Kartu statistik besar | Boleh menjadi ringkasan tanggal terpilih (P8, Should), bukan pengganti peta. |
| Angka dari gambar | Tidak digunakan sebagai data nyata. Semua angka berasal dari API/database. |
| Kategori “Sangat Tinggi” | Tidak digunakan. PRD menetapkan Rendah, Sedang, dan Tinggi. |
| Halaman pelatihan/pengaturan model | Tidak dibuat. Pelatihan model dikerjakan terpisah dari website. |
| Unggah dataset melalui halaman web | Opsional (A14, Could). Impor utama tetap melalui perintah Artisan. |
| Halaman hasil prediksi terpisah | Informasi hasil diintegrasikan ke peta publik dan area admin sesuai PRD. |
| Nama merek “MNTN” | Hanya placeholder; gunakan identitas proyek yang disepakati tim. |

### 2.3 Pemetaan delapan layar referensi

| Layar | Keputusan |
|---|---|
| Login | Dipertahankan untuk admin di `/admin/login`. |
| Dashboard publik | Diadaptasi sebagai landing page opsional, bukan pusat pengalaman publik. |
| Peta dan detail wilayah | Dipertahankan sebagai halaman utama `/peta`. |
| Dialog unduh | Dihilangkan karena unduhan publik tidak termasuk PRD. |
| Dashboard admin | Dipertahankan dengan informasi operasional sesuai A2. |
| Pengelolaan dataset | Diadaptasi menjadi pengelolaan data harian per tanggal. |
| Halaman model | Tidak dibuat; pelatihan model berada di luar website. |
| Hasil prediksi | Diintegrasikan ke peta publik dan status admin. |

---

## 3. Struktur navigasi dan halaman

### 3.1 Navigasi publik

Navigasi publik ditampilkan pada `/peta` dan `/tentang`:
- Identitas/nama proyek di sisi kiri.
- **Peta Risiko ROB** menuju `/peta`.
- **Tentang Data** menuju `/tentang`.
- Jika admin sudah login, tombol **Admin** dengan menu Dashboard admin, Kelola data, dan Logout.

Tidak ada tautan Login, Register, atau Unduh pada navigasi publik. Logout harus berupa form POST dengan CSRF, bukan tautan GET.

### 3.2 Navigasi admin

Setelah login, tombol Admin muncul pada navigasi. Area admin menyediakan:
- Dashboard admin (`/admin`).
- Kelola data (`/admin/data`).
- Riwayat impor (`/admin/impor`).
- Log aktivitas (`/admin/aktivitas`).
- Logout.

Riwayat impor dan log aktivitas berstatus Should sesuai PRD. Tampilkan penanda halaman aktif.

### 3.3 Daftar halaman

| Halaman | Rute | Pengguna | Prioritas PRD |
|---|---|---|---|
| Peta prediksi ROB | `/peta` | Publik | P1–P6 Must; P7–P10 Should |
| Tentang Data | `/tentang` | Publik | P10 Should |
| Beranda/landing page | `/` | Publik | P11 Could |
| Login admin | `/admin/login` | Admin | A1 Must |
| Dashboard admin | `/admin` | Admin | A2 Should |
| Kelola data — daftar tanggal | `/admin/data` | Admin | A3 Must |
| Kelola data — satu tanggal | `/admin/data/{tanggal}` | Admin | A4–A9 |
| Riwayat impor | `/admin/impor` | Admin | A11 Should |
| Detail laporan impor | `/admin/impor/{id}` | Admin | A11 Should |
| Log aktivitas | `/admin/aktivitas` | Admin | A13 Should |

Semua rute `/admin/*` selain login wajib dilindungi middleware autentikasi. Tidak ada halaman registrasi pengguna.

---

## 4. Arah visual dan design tokens

Nilai berikut adalah **usulan visual** dari gambar referensi, bukan ketentuan teknis PRD. Nilai boleh disesuaikan selama hierarki, kontras, makna status, dan keterbacaan tetap terjaga.

### 4.1 Tema

Gunakan **Dark Navy Dashboard**:
- Background halaman dominan navy/biru gelap.
- Header dan panel menggunakan variasi biru gelap.
- Kartu sedikit lebih terang daripada background.
- Border tipis biru untuk memisahkan panel.
- Aksen biru terang untuk tombol utama, tautan, fokus, dan elemen interaktif.
- Gradasi/glow lembut hanya pada area terpilih.
- Teks utama putih; teks sekunder biru keabu-abuan.
- Form dan tabel harus tetap kontras dan mudah dibaca.

### 4.2 Palet warna

| Token | Usulan | Penggunaan |
|---|---|---|
| `--color-page` | `#061A35` | Latar utama |
| `--color-page-secondary` | `#0B2852` | Header, hero, area sekunder |
| `--color-surface` | `#10366B` | Kartu dan panel |
| `--color-surface-raised` | `#16447F` | Panel yang lebih menonjol |
| `--color-border` | `#315B91` | Border kartu dan input |
| `--color-text` | `#FFFFFF` | Teks utama |
| `--color-muted` | `#B7C8DF` | Teks sekunder |
| `--color-brand` | `#38BDF8` | Aksen, tautan, fokus, tombol utama |
| `--color-brand-hover` | `#0EA5E9` | Hover tombol/tautan |
| `--color-low` | `#22C55E` | Tingkat Rendah |
| `--color-medium` | `#EAB308` | Tingkat Sedang |
| `--color-high` | `#F97316` | Tingkat Tinggi |
| `--color-error` | `#EF4444` | Galat |
| `--color-warning` | `#F59E0B` | Peringatan |
| `--color-info` | `#38BDF8` | Informasi |

PRD hanya menetapkan `rendah`, `sedang`, dan `tinggi`. Jangan menambahkan “Sangat Tinggi” atau kategori lain karena gambar referensi.

Warna risiko adalah penanda kategori hasil model. Warna tombol dan status proses harus dibedakan agar maknanya tidak membingungkan.

### 4.3 Tipografi, bentuk, dan jarak

- Font sans-serif yang mudah dibaca, misalnya Inter, system-ui, atau font sistem.
- H1: 28–36 px desktop, 24–28 px mobile.
- H2: 20–24 px; H3: 16–18 px; isi: 14–16 px; metadata minimal 12 px.
- Judul tegas, tidak dekoratif berlebihan.
- Grid dan jarak konsisten dalam kelipatan 4 atau 8 px.
- Kartu radius 12–16 px.
- Input dan tombol tinggi minimal 40–44 px.
- Border tipis dan bayangan lembut.
- Tombol utama memiliki kontras jelas dan label berupa kata kerja.

### 4.4 Makna warna tingkat

| Tingkat | Label wajib | Warna |
|---|---|---|
| Rendah | Rendah | Hijau |
| Sedang | Sedang | Kuning/amber |
| Tinggi | Tinggi | Oranye |

Legenda, tooltip, dan panel detail harus menyertakan teks tingkat. Jangan mengandalkan warna saja.

---

## 5. Halaman publik — Peta prediksi ROB (`/peta`)

### 5.1 Tujuan

Halaman utama sistem. Pengunjung memilih tanggal yang tersedia, menekan **Cari**, lalu melihat hasil prediksi historis untuk 31 kelurahan Jakarta Utara.

### 5.2 Susunan desktop

1. Header/navigasi publik.
2. Judul halaman dan penjelasan singkat.
3. Keterangan **Data historis**.
4. Banner simulasi atau catatan kedaluwarsa jika berlaku.
5. Kontrol tanggal: date picker dan tombol Cari.
6. Ringkasan hasil tanggal terpilih jika P8 sudah tersedia (Should).
7. Area utama: peta Leaflet dominan dan panel detail di sisi kanan/samping.
8. Pencarian/filter wilayah jika P9 tersedia (Should).
9. Legenda dan metadata hasil.
10. Footer ringkas berisi disclaimer dan tautan Tentang Data.

Peta mendapat ruang paling besar. Panel detail tidak boleh menutupi sebagian besar peta.

### 5.3 Susunan mobile

- Header ringkas dengan menu navigasi.
- Judul, keterangan historis, dan banner bila berlaku.
- Kontrol tanggal ditumpuk vertikal.
- Peta memenuhi lebar layar dengan tinggi memadai.
- Panel detail berada di bawah peta atau sebagai bottom sheet.
- Pencarian/filter ditampilkan vertikal atau dalam panel yang dapat dibuka.
- Legenda tetap mudah ditemukan.
- Detail fitur ditampilkan sebagai daftar label-nilai, bukan tabel lebar.

### 5.4 Kontrol tanggal (P2 — Must)

- Label: **Pilih tanggal data**.
- Date picker hanya menawarkan tanggal yang mempunyai hasil prediksi valid untuk versi model aktif.
- Tombol utama: **Cari**.
- Tombol Cari nonaktif ketika permintaan berlangsung.
- Jika belum ada tanggal tersedia, date picker nonaktif dan tampilkan pesan yang sesuai.
- Tampilkan keterangan **Data historis** di dekat kontrol tanggal agar tidak disalahartikan sebagai tanggal prakiraan masa depan.

Tanggal pertukaran data tetap `YYYY-MM-DD`. Tampilan kepada pengguna boleh memakai format lokal yang mudah dibaca.

### 5.5 Ringkasan tanggal (P8 — Should)

Jika tersedia, tampilkan:
- Jumlah kelurahan tingkat Rendah.
- Jumlah kelurahan tingkat Sedang.
- Jumlah kelurahan tingkat Tinggi.
- Total wilayah: 31 kelurahan.

Jumlah berasal dari endpoint ringkasan untuk tanggal terpilih. Jangan menggunakan angka permanen atau angka dari gambar. Kartu ringkasan tidak boleh lebih dominan daripada peta.

### 5.6 Peta dan poligon (P1 — Must)

- Gunakan Leaflet dengan GeoJSON batas kelurahan.
- Peta menampilkan 31 kelurahan.
- Cocokkan hasil dengan poligon melalui `properties.kode_wilayah`, bukan nama atau urutan.
- Sebelum hasil dipilih, poligon berwarna abu-abu.
- Setelah hasil berhasil dimuat, poligon diberi warna menurut tingkat risiko.
- Klik poligon memilih kelurahan dan memperbarui panel detail.
- Hover desktop boleh menampilkan tooltip nama kelurahan dan tingkat; tooltip bukan pengganti panel detail.
- Sediakan outline agar poligon berdampingan tetap terlihat.
- Jangan menyatakan peta valid jika GeoJSON gagal validasi.
- Jika GeoJSON gagal dimuat, sediakan akses detail melalui daftar wilayah bila data tersedia, sesuai status tampilan PRD.

### 5.7 Legenda (P3 — Must)

Legenda selalu menyertakan warna dan teks:
- Hijau — Rendah.
- Kuning/amber — Sedang.
- Oranye — Tinggi.

Jelaskan bahwa warna menunjukkan **tingkat hasil model**, bukan status peringatan resmi.

### 5.8 Panel detail kelurahan (P4 — Must)

Sebelum ada kelurahan dipilih, tampilkan:
> Pilih salah satu kelurahan pada peta untuk melihat detail.

Setelah dipilih, tampilkan:
- Nama kelurahan.
- Kecamatan.
- Tingkat: Rendah/Sedang/Tinggi.
- Probabilitas dalam persentase.
- Elevasi (m).
- Jarak pantai (km).
- Curah hujan (mm/hari).
- Pasang tertinggi (m).
- Gelombang tertinggi (m).

Gunakan snapshot fitur yang tersimpan bersama prediksi. Jangan mengambil nilai fitur terbaru secara terpisah karena dapat berbeda dari nilai yang digunakan saat prediksi.

Probabilitas adalah keluaran numerik model, sedangkan tingkat adalah kategori yang dikembalikan layanan. **Jangan menetapkan ambang numerik di UI atau menghitung ulang kategori di frontend.** Nilai ambang dan mekanisme kategorisasi mengikuti metadata model/aturan stub pada PRD dan layanan prediksi.

Jangan menambahkan klaim sebab-akibat seperti “gelombang menyebabkan tingkat tinggi” hanya dari nilai fitur.

### 5.9 Pencarian dan filter wilayah (P9 — Should)

Jika diimplementasikan, sediakan:
- Kolom pencarian nama kelurahan.
- Filter kecamatan.
- Filter tingkat: Semua, Rendah, Sedang, Tinggi.
- Daftar hasil sesuai pencarian/filter.
- Aksi memilih hasil untuk memusatkan perhatian pada poligon dan memperbarui panel detail.

Ketentuan:
- Filter hanya menyaring tampilan/daftar berdasarkan hasil yang tersedia; tidak mengubah hasil prediksi.
- Jangan menghapus data sumber karena filter dipilih.
- Sediakan keadaan “Tidak ada kelurahan yang cocok”.
- Jelaskan bahwa kelurahan yang tersembunyi oleh filter tetap bagian dari cakupan 31 kelurahan.
- P9 berstatus Should, bukan syarat inti Must.

### 5.10 Metadata hasil (P7 — Should)

Tampilkan jika tersedia:
- Jenis data: **Historis**.
- Versi model, misalnya `stub-v0`.
- Waktu pembaruan dalam WIB.
- Status hasil jika kedaluwarsa.

Jika hasil simulasi, tampilkan banner jelas:

> **HASIL SIMULASI** — Hasil ini berasal dari layanan prediksi tiruan (stub), bukan keluaran model Machine Learning.

Jika hasil kedaluwarsa:

> Hasil belum diperbarui dengan data masukan terbaru. Informasi yang ditampilkan merupakan hasil terakhir yang masih tersimpan.

Banner simulasi dan catatan kedaluwarsa tidak boleh hanya disembunyikan dalam tooltip. P7 berstatus Should pada daftar fitur PRD.

### 5.11 Landing page (`/`, P11 — Could)

Jika dibuat:
- Tampilkan identitas dan tujuan proyek secara ringkas.
- Statistik ringkas hanya jika datanya berasal dari sistem.
- Sediakan tombol menuju **Peta Risiko ROB** (`/peta`).
- Jangan menjadikan landing page pengganti halaman peta.
- Jangan menambahkan angka contoh atau klaim akurasi tanpa sumber.

Jika tidak dibuat, `/` boleh mengarahkan pengguna ke `/peta` sesuai PRD.

---

## 6. Status antarmuka peta

| Status | Tampilan dan perilaku |
|---|---|
| Awal, belum memilih tanggal | Poligon abu-abu; pesan “Pilih tanggal lalu tekan Cari.” |
| Memuat | Indikator loading di atas peta; tombol Cari nonaktif; peta lama (jika ada) diredupkan. |
| Kosong: tanggal tidak memiliki hasil | Pesan “Data untuk tanggal ini belum tersedia”; peta kembali abu-abu. |
| Kosong: tidak ada tanggal tersedia | Pesan “Belum ada data prediksi”; date picker nonaktif. |
| Galat jaringan/server | Pesan ramah dan tombol “Coba lagi”; peta lama tetap tampil bila tersedia. |
| Layanan prediksi tidak tersedia (503) | Pesan “Prediksi sedang tidak tersedia”; hasil tersimpan tetap dapat dibuka sesuai respons sistem. |
| Galat memuat GeoJSON | Pesan khusus dan tombol coba lagi; detail tetap dapat diakses melalui daftar wilayah bila data tersedia. |
| Hasil berhasil | Peta berwarna, legenda, versi model dan waktu pembaruan jika tersedia. |
| Hasil simulasi | Banner simulasi terlihat. |
| Hasil kedaluwarsa | Catatan bahwa hasil belum diperbarui dengan data masukan terbaru. |

Jangan menampilkan stack trace, path server, token, atau detail teknis internal. Jangan menampilkan hasil parsial sebagai hasil lengkap 31 kelurahan. Hasil lama hanya dipertahankan sesuai aturan PRD dan harus diberi catatan status yang sesuai.

---

## 7. Halaman Tentang Data (`/tentang` — P10, Should)

Halaman ini menjelaskan konteks dan batasan hasil. Gunakan bagian/kartu yang mudah dipindai.

### 7.1 Konten

1. **Tentang proyek**
   - Tujuan website dan cakupan 31 kelurahan Jakarta Utara.
   - Penjelasan bahwa sistem merupakan proyek akademik.

2. **Jenis data**
   - Hasil merupakan prediksi atas data historis tahun 2026.
   - Bukan prakiraan masa depan atau prediksi real-time.
   - Jika masih stub, jelaskan bahwa hasil belum berasal dari model ML.

3. **Fitur masukan**
   - Elevasi (m).
   - Jarak pantai (km).
   - Curah hujan (mm/hari).
   - Pasang tertinggi (m).
   - Gelombang tertinggi (m).
   - Cantumkan sumber, cakupan/resolusi, periode, dan catatan sesuai `data/catatan_dataset.md` ketika tersedia.

4. **Metode**
   - Sebelum model asli tersedia: “Model Machine Learning belum tersedia; sistem menggunakan simulasi.”
   - Setelah model tersedia: jenis model, periode latih/uji, metrik evaluasi, dan cara tingkat diturunkan dari probabilitas berdasarkan informasi tim ML.
   - Penjelasan threshold/kategorisasi harus mengikuti metadata model dan keputusan tim ML yang benar-benar digunakan. Jangan menampilkan angka ambang yang belum disepakati atau belum tercatat.

5. **Keterbatasan**
   - Data 2026 tidak berlabel sehingga akurasinya tidak dapat dihitung dari data tersebut.
   - Data masukan harian dapat sama pada beberapa kelurahan.
   - Label kejadian bersumber dari laporan dan dapat memiliki bias pelaporan.
   - Hasil bukan peringatan resmi.

6. **Sumber dan atribusi**
   - Cantumkan sumber data dan atribusi sesuai lisensi.
   - Jangan mengarang sumber atau tanggal pembaruan.

7. **Disclaimer — teks wajib PRD**

> Hasil ini adalah prediksi model atas data historis untuk keperluan akademik, bukan peringatan resmi. Rujuk BMKG/BPBD untuk informasi resmi.

### 7.2 Gaya konten

- Gunakan bahasa Indonesia lugas.
- Jelaskan istilah teknis secara singkat.
- Hindari klaim “akurasi tinggi”, “pasti banjir”, atau “aman” tanpa dukungan evaluasi sah.
- Jangan menyamakan probabilitas model dengan kepastian kejadian.
- Jangan mengisi sumber, periode, metrik, atau ambang yang belum tersedia dari dokumentasi/tim.

---

## 8. Halaman login admin (`/admin/login`)

### 8.1 Arah layout

- Latar navy/biru gelap dengan gradasi lembut.
- Form login di tengah layar dalam kartu biru gelap yang kontras.
- Border kartu tipis dan sudut membulat.
- Identitas proyek di atas form.
- Form tidak terlalu lebar; fokus pada email dan password.
- Tombol utama biru terang atau aksen kontras.
- Tidak menampilkan statistik publik yang tidak diperlukan.

### 8.2 Elemen

- Judul **Login Admin**.
- Input email.
- Input password.
- Tombol **Masuk**.
- Pesan validasi di dekat input terkait.
- Pesan umum jika kredensial salah.
- Indikator proses saat form dikirim.

Tidak ada registrasi, login pengguna umum, tombol login pada navigasi publik, atau link reset password kecuali kelak disepakati dan benar-benar diimplementasikan.

### 8.3 Perilaku

- Validasi sisi server.
- Password menggunakan mekanisme hashing dan autentikasi Laravel.
- Terapkan throttle login.
- Setelah berhasil, arahkan ke dashboard admin.
- Jangan membocorkan apakah email tertentu terdaftar melalui pesan galat yang terlalu spesifik.
- Jangan menampilkan detail teknis internal pada pesan galat.

---

## 9. Navigasi admin dan dashboard (`/admin`)

### 9.1 Navigasi admin

Menu berisi Dashboard admin, Kelola data, dan Logout. Di area admin, gunakan navigasi konsisten dan penanda halaman aktif. Tautan Riwayat Impor dan Log Aktivitas ditampilkan bila halaman Should tersebut diimplementasikan.

### 9.2 Dashboard admin (A2 — Should)

Informasi sesuai PRD:
- Kelengkapan data untuk tanggal yang relevan.
- Status prediksi per tanggal.
- Model aktif dan versinya.
- Mode layanan prediksi: stub/model.
- Status kesehatan layanan Python.
- Waktu dan ringkasan impor terakhir.

Kelengkapan data dan status prediksi adalah dua indikator berbeda dan tidak boleh digabung.

Contoh kelompok kartu:
- Kelengkapan data: `n/31 kelurahan`.
- Status prediksi: Belum dihitung / Menghitung / Berhasil / Kedaluwarsa / Gagal.
- Layanan prediksi: Aktif / Tidak tersedia.
- Model aktif: `stub-v0` atau versi model aktif.
- Impor terakhir: waktu dan ringkasan dari data impor.

Semua nilai berasal dari database atau pemeriksaan layanan. Jangan gunakan angka statis. Dashboard admin berstatus Should; fungsi Must pada daftar tanggal dan halaman pengelolaan data tetap menjadi prioritas.

### 9.3 Label status prediksi

| Status teknis | Label UI |
|---|---|
| `belum_dihitung` | Belum dihitung |
| `menghitung` | Sedang menghitung |
| `berhasil` | Berhasil |
| `kedaluwarsa` | Kedaluwarsa |
| `gagal` | Gagal |

Tambahkan lencana **SIMULASI** bila hasil menggunakan stub. Warna status hanya penguat visual; teks tetap wajib.

---

## 10. Halaman Kelola Data — daftar tanggal (`/admin/data`)

### 10.1 Tujuan

Memberi admin gambaran tanggal yang sudah memiliki data, kelengkapan data, dan status prediksi.

### 10.2 Tabel

Kolom:
1. Tanggal.
2. Kelengkapan data (`n/31`).
3. Status prediksi.
4. Penanda simulasi bila relevan.
5. Waktu dihitung.
6. Aksi **Edit** dan **Hitung ulang**.

Kelengkapan dan status prediksi harus menjadi kolom terpisah.

### 10.3 Perilaku

- Edit membuka halaman tanggal terkait.
- Hitung ulang hanya aktif jika data lengkap 31/31.
- Jika tidak lengkap, tampilkan alasan/indikator.
- Menghapus seluruh data satu tanggal harus meminta konfirmasi.
- Jika hitung ulang gagal, hasil lama tidak boleh hilang.
- Tampilkan penanda simulasi bila status/hasil berasal dari stub.

### 10.4 Hapus data tanggal

PRD menyediakan `DELETE /admin/data/{tanggal}` untuk menghapus seluruh data satu tanggal:
- Label tindakan harus jelas, misalnya **Hapus seluruh data tanggal ini**.
- Dialog konfirmasi menampilkan tanggal dan konsekuensi penghapusan.
- Jangan menyamarkan penghapusan seluruh tanggal sebagai penghapusan satu kelurahan.
- Setelah berhasil, perbarui daftar tanggal dan informasi kelengkapan/status.

**Catatan konsistensi PRD:** A4 menyebut tabel 31 kelurahan dapat ditambah, diubah, dan dihapus, sedangkan rute penghapusan yang dirinci adalah penghapusan seluruh data satu tanggal. UI ini tidak mengarang endpoint atau perilaku penghapusan per baris yang belum ditentukan. Detail penghapusan per baris harus mengikuti keputusan/rute backend yang benar-benar tersedia.

---

## 11. Halaman Kelola Data — satu tanggal (`/admin/data/{tanggal}`)

### 11.1 Prioritas konten

1. Judul tanggal.
2. Ringkasan kelengkapan `n/31`.
3. Daftar kelurahan yang belum memiliki data di bagian atas.
4. Form **Isi cepat** (A7, Should).
5. Tabel/form data 31 kelurahan.
6. Tombol **Simpan perubahan**.
7. Tombol **Hitung ulang** dengan status dan keterangan.

### 11.2 Kolom tabel

- Kelurahan.
- Kecamatan.
- Stasiun pasut.
- Curah hujan (mm/hari).
- Pasang tertinggi (m).
- Gelombang tertinggi (m).

Elevasi dan jarak pantai bersifat statis dan tidak diedit melalui halaman ini. Nilainya berasal dari data kelurahan yang diisi melalui seeder.

### 11.3 Daftar kelurahan tanpa data (A5 — Must)

- Tampilkan di bagian atas halaman.
- Tampilkan nama kelurahan dan kecamatan.
- Daftar berasal dari perbandingan 31 kelurahan dengan data harian pada tanggal tersebut.
- Jika seluruh data tersedia, tampilkan pesan bahwa 31 kelurahan sudah memiliki data.
- Jangan mengisi daftar dengan contoh statis.

### 11.4 Isi cepat (A7 — Should)

Jika diimplementasikan:
- Admin dapat mengisi nilai gelombang sekali untuk tanggal tersebut dan menerapkannya pada baris terkait.
- Nilai pasang diisi berdasarkan stasiun pasut kelurahan: Sunda Kelapa atau Kolinlamil.
- Tampilkan nilai/stasiun target sebelum menerapkan perubahan.
- Jangan menimpa nilai yang sudah diisi tanpa konfirmasi jelas.
- Beri umpan balik mengenai baris yang terdampak.

Isi cepat berstatus Should dan bukan pengganti pengeditan tiap baris.

### 11.5 Validasi dan pesan (A8 — Must)

- Semua nilai wajib terisi.
- Curah hujan ≥ 0.
- Gelombang tertinggi ≥ 0.
- Pasang tertinggi berada dalam rentang wajar yang disepakati tim ML.
- Desimal menggunakan titik; desimal koma ditolak.
- Label input selalu menyertakan satuan.
- Kesalahan ditampilkan dekat field bermasalah dan diringkas di bagian atas form.
- Simpan perubahan tidak boleh diam-diam menghilangkan data yang gagal divalidasi.

Validasi frontend hanya membantu pengguna. Validasi server tetap menjadi otoritas.

### 11.6 Simpan dan hitung ulang (A9 dan A6 — Must)

Saat menyimpan:
1. Validasi masukan.
2. Simpan data.
3. Coba hitung ulang prediksi untuk tanggal itu.
4. Jika layanan Python tidak tersedia, data tetap tersimpan dan status menjadi `kedaluwarsa` atau `belum_dihitung` sesuai keadaan sebelumnya dan aturan PRD.
5. Tampilkan pesan bahwa data tersimpan tetapi prediksi belum diperbarui.

Tombol **Hitung ulang** hanya aktif bila data lengkap 31/31. Tampilkan status proses dan hasilnya.

Jangan menampilkan “prediksi berhasil” hanya karena data berhasil disimpan. Penyimpanan data dan keberhasilan prediksi adalah dua hasil operasi yang berbeda.

### 11.7 Penghapusan data

- Tindakan penghapusan meminta konfirmasi.
- Ruang lingkup penghapusan harus tertulis jelas.
- Penghapusan seluruh data satu tanggal mengikuti rute dan perilaku PRD.
- Jangan menampilkan aksi hapus per baris jika backend belum menyediakan perilaku/rute yang sesuai.
- Setelah perubahan/penghapusan, tampilkan kelengkapan terbaru dan status prediksi dari sistem.

---

## 12. Halaman riwayat impor (`/admin/impor` — A11, Should)

### 12.1 Daftar impor

Kolom:
- Waktu impor.
- Nama berkas.
- Sumber/keterangan.
- Jumlah baris.
- Berhasil.
- Galat.
- Status.
- Aksi **Lihat laporan**.

### 12.2 Detail laporan (`/admin/impor/{id}`)

Tampilkan:
- Nama berkas dan sumber.
- Waktu mulai dan selesai.
- Jumlah baris.
- Jumlah berhasil dan galat.
- Status impor.
- Daftar galat per baris sesuai batas penyimpanan PRD.

Jangan tampilkan rahasia atau path absolut server. Unggah CSV melalui halaman web berstatus Could dan bukan kebutuhan inti; impor utama dilakukan lewat Artisan.

### 12.3 Unggah CSV melalui web (A14 — Could)

Jika dibuat:
- Tandai sebagai fitur opsional.
- Sediakan pratinjau sebelum impor.
- Jelaskan format dan kesalahan validasi.
- Jangan menggantikan mekanisme impor utama melalui Artisan.
- Jangan menyatakan impor berhasil sebelum server mengonfirmasi hasilnya.

---

## 13. Halaman log aktivitas (`/admin/aktivitas` — A13, Should)

Halaman Should untuk audit aktivitas admin.

Kolom:
- Waktu (WIB).
- Admin.
- Aksi.
- Tabel/objek terkait.
- Ringkasan detail.

Log dapat difilter atau dipaginasi jika jumlah data meningkat. Jangan tampilkan rahasia, token, password, atau data teknis sensitif.

---

## 14. Komponen UI yang dapat digunakan ulang

| Komponen | Fungsi |
|---|---|
| `PublicHeader` | Navigasi publik dan menu Admin bila terautentikasi |
| `AdminNavigation` | Navigasi area admin |
| `PageHeader` | Judul, deskripsi, dan aksi halaman |
| `DateSearchForm` | Date picker dan tombol Cari |
| `SimulationBanner` | Penanda hasil stub |
| `StaleResultNotice` | Pemberitahuan hasil kedaluwarsa |
| `RiskLegend` | Legenda Rendah/Sedang/Tinggi |
| `RiskBadge` | Label tingkat dengan warna dan teks |
| `MapView` | Peta Leaflet dan layer GeoJSON |
| `KelurahanDetailPanel` | Detail wilayah dan snapshot lima fitur |
| `DateSummary` | Jumlah wilayah per tingkat |
| `RegionSearchFilter` | Pencarian kelurahan dan filter kecamatan/tingkat (P9, Should) |
| `CompletenessIndicator` | Kelengkapan data `n/31` |
| `PredictionStatusBadge` | Status prediksi |
| `ServiceHealthIndicator` | Status layanan Python |
| `EmptyState` | Tampilan belum ada data |
| `LoadingState` | Indikator proses |
| `ErrorState` | Pesan galat dan aksi coba lagi |
| `ConfirmDialog` | Konfirmasi aksi destruktif |
| `FormField` | Label, input, satuan, bantuan, dan galat |
| `ImportStatus` | Ringkasan status impor |
| `ActivityLogTable` | Daftar log aktivitas |

Komponen Should/Could hanya perlu dibuat jika fitur terkait dikerjakan. Komponen dapat diwujudkan sebagai Blade components, partials, atau komponen JavaScript.

---

## 15. Format angka, tanggal, dan bahasa

- Bahasa UI: Indonesia.
- Tanggal yang dikirim/diterima sistem: `YYYY-MM-DD`.
- Tampilan tanggal boleh menggunakan format lokal, misalnya `1 November 2026`, tetapi nilai API/database tetap ISO.
- Waktu ditampilkan dalam WIB.
- Probabilitas ditampilkan sebagai persentase, misalnya `82%`; simpan nilai mentah 0–1 sesuai kontrak API.
- Jangan menghitung ulang tingkat risiko dari probabilitas di frontend. Gunakan `tingkat` dari layanan.
- Satuan fitur harus selalu terlihat:
  - Elevasi: m.
  - Jarak pantai: km.
  - Curah hujan: mm/hari.
  - Pasang tertinggi: m.
  - Gelombang tertinggi: m.
- Jangan mengubah satuan atau melakukan pembulatan yang mengubah makna data tanpa aturan yang disepakati.
- Desimal pada input menggunakan titik; desimal koma ditolak sesuai PRD.

---

## 16. Responsivitas dan aksesibilitas

### 16.1 Responsivitas

**Desktop/laptop**
- Navigasi horizontal.
- Peta dan panel detail berdampingan.
- Tabel admin menampilkan seluruh kolom utama.
- Ringkasan dapat ditampilkan dalam beberapa kartu.
- Pencarian/filter dapat diletakkan di atas peta atau panel samping.

**Tablet**
- Peta tetap dominan.
- Panel detail berpindah ke bawah atau menjadi drawer.
- Tabel dapat memakai scroll horizontal dengan header jelas.
- Kontrol tanggal/filter dapat disusun dua kolom jika ruang cukup.

**Ponsel**
- Navigasi ringkas.
- Kontrol tanggal ditumpuk vertikal.
- Peta memenuhi lebar layar.
- Detail kelurahan berada di bawah peta atau bottom sheet.
- Tabel admin dapat berubah menjadi kartu per kelurahan; jangan memaksa tabel desktop menyusut.
- Pencarian/filter ditampilkan vertikal.
- Tombol dan target sentuh memiliki ukuran nyaman.

### 16.2 Aksesibilitas

- Semua input memiliki label yang terhubung.
- Tombol memiliki nama jelas.
- Fokus keyboard terlihat dan urutan tab logis.
- Kontras cukup, khususnya pada tema gelap.
- Status tidak hanya dibedakan melalui warna.
- Peta memiliki alternatif akses berbasis daftar kelurahan/detail.
- Pesan loading/galat diumumkan dengan mekanisme aksesibilitas yang sesuai.
- Metadata penting tidak menggunakan teks terlalu kecil.
- Tooltip bukan satu-satunya tempat informasi penting.

---

## 17. Aturan interaksi dan umpan balik

1. Setiap aksi simpan, cari, impor, dan hitung ulang memberi umpan balik.
2. Tombol yang sedang memproses menunjukkan status dan mencegah pengiriman ganda.
3. Aksi hapus meminta konfirmasi dan menjelaskan ruang lingkup penghapusan.
4. Validasi server tetap menjadi otoritas; validasi frontend hanya membantu.
5. Galat menjelaskan langkah selanjutnya tanpa membocorkan detail teknis.
6. Jangan tampilkan pesan sukses sebelum server mengonfirmasi keberhasilan.
7. Jika layanan Python mati, admin tetap dapat menyimpan data masukan.
8. Hasil lama tetap ditampilkan bila sesuai PRD, dengan catatan kedaluwarsa jika diperlukan.
9. Jangan menampilkan hasil parsial sebagai hasil lengkap 31 kelurahan.
10. Jangan mencampur hasil simulasi dan model asli dalam satu versi model.
11. Pencarian/filter hanya mengubah tampilan daftar/peta, bukan data atau hasil model.
12. Saat tanggal diganti, detail yang ditampilkan harus mengikuti tanggal yang berhasil dimuat; jangan mencampur detail dari tanggal sebelumnya.
13. Jika permintaan gagal dan hasil lama dipertahankan, jelaskan bahwa tampilan adalah hasil terakhir yang tersimpan.
14. Untuk penyimpanan yang diikuti hitung ulang, tampilkan hasil penyimpanan dan status prediksi secara terpisah.

---

## 18. Konten dan istilah baku

| Istilah teknis/status | Label UI |
|---|---|
| `rendah` | Rendah |
| `sedang` | Sedang |
| `tinggi` | Tinggi |
| `belum_dihitung` | Belum dihitung |
| `menghitung` | Sedang menghitung |
| `berhasil` | Berhasil |
| `kedaluwarsa` | Kedaluwarsa |
| `gagal` | Gagal |
| `is_simulasi = true` | SIMULASI / Hasil simulasi |
| `jenis_data = historis` | Data historis |

Gunakan istilah **Peta Risiko ROB** sebagai label navigasi/halaman. Ini pilihan komunikasi UI dan tidak mengubah definisi atau keluaran teknis model pada PRD.

Hindari:
- “Prediksi besok” atau “prakiraan real-time”.
- “Pasti banjir” atau “pasti aman”.
- “Akurasi 2026” karena data 2026 tidak berlabel.
- “Sangat Tinggi” sebagai kategori default.
- “Unduh hasil” untuk pengunjung publik.
- Menyebut hasil stub sebagai hasil model Machine Learning.
- Menampilkan probabilitas sebagai kepastian kejadian.

---

## 19. Batasan desain yang wajib dipertahankan

- Tidak ada registrasi pengguna umum.
- Tidak ada login pada navigasi publik.
- Login admin hanya di `/admin/login`.
- Tidak ada unduhan publik CSV/PNG/PDF.
- Tidak ada notifikasi atau aplikasi mobile pada versi ini.
- Tidak ada pengambilan data otomatis dari API eksternal.
- Admin mengelola data masukan harian, bukan dataset pelatihan atau label.
- Dataset pelatihan tidak dimasukkan ke database website.
- Hasil stub selalu diberi penanda simulasi di peta dan admin.
- Peta menampilkan data historis, bukan peringatan atau prakiraan masa depan.
- Disclaimer resmi harus ditampilkan.
- Semua fungsi inti tetap dapat diuji ketika layanan Python tidak tersedia.
- Tidak ada kategori risiko selain Rendah, Sedang, dan Tinggi kecuali PRD diperbarui.
- UI tidak menentukan nilai ambang probabilitas sendiri.
- UI tidak mengubah aturan validasi, API, database, atau alur prediksi pada PRD.
- Fitur Should/Could tidak diperlakukan sebagai syarat Must.

---

## 20. Checklist penerimaan UI

Checklist mengikuti prioritas PRD v0.4. Butir Should/Could tidak boleh menghambat demonstrasi fungsi Must.

### 20.1 Publik — Must

- [ ] Pengunjung dapat membuka `/peta` tanpa login.
- [ ] Peta awal menampilkan poligon abu-abu dan instruksi memilih tanggal.
- [ ] Date picker hanya berisi tanggal yang memiliki hasil valid.
- [ ] Tombol Cari memuat hasil dan menunjukkan loading.
- [ ] Peta menampilkan 31 kelurahan berdasarkan `kode_wilayah`.
- [ ] Legenda memiliki label Rendah, Sedang, Tinggi.
- [ ] Klik kelurahan menampilkan nama, kecamatan, tingkat, probabilitas, dan lima fitur beserta satuan.
- [ ] Status awal, loading, kosong, galat, layanan tidak tersedia, dan GeoJSON gagal memiliki tampilan sesuai PRD.
- [ ] Hasil simulasi memiliki banner jelas.
- [ ] Hasil kedaluwarsa memiliki catatan.
- [ ] Tidak ada unduhan publik.
- [ ] UI menggunakan tingkat dari API dan tidak menghitung ulang kategori dari probabilitas.

### 20.2 Publik — Should

- [ ] Ringkasan jumlah kelurahan per tingkat.
- [ ] Versi model dan waktu pembaruan dalam WIB.
- [ ] Pencarian kelurahan tersedia.
- [ ] Filter kecamatan tersedia.
- [ ] Filter tingkat risiko tersedia.
- [ ] Halaman Tentang Data berisi sumber, metode, keterbatasan, atribusi, dan disclaimer sesuai data yang tersedia.

### 20.3 Publik — Could

- [ ] Landing page `/` tersedia atau mengarahkan pengguna ke `/peta`.
- [ ] Statistik ringkas landing page berasal dari data aktual.

### 20.4 Admin — Must

- [ ] Login admin tersedia di `/admin/login`, tanpa tautan login publik.
- [ ] Rute admin terlindungi autentikasi.
- [ ] Daftar tanggal memisahkan kelengkapan `n/31` dan status prediksi.
- [ ] Halaman tanggal menampilkan kelurahan yang belum memiliki data.
- [ ] Form mengelola data harian untuk 31 kelurahan sesuai PRD.
- [ ] Validasi dan satuan terlihat jelas.
- [ ] Hitung ulang hanya aktif bila data lengkap.
- [ ] Penanda simulasi terlihat pada hasil stub.
- [ ] Jika layanan Python mati, data tetap dapat disimpan dan status diperbarui.
- [ ] Hasil lama tidak ditampilkan seolah-olah terbaru jika statusnya kedaluwarsa/gagal.
- [ ] Penghapusan seluruh data tanggal meminta konfirmasi.

### 20.5 Admin — Should

- [ ] Dashboard menampilkan kelengkapan, status prediksi, model aktif, status layanan Python, dan impor terakhir.
- [ ] Riwayat impor dan laporan galat dapat dilihat.
- [ ] Log aktivitas tersedia.
- [ ] Isi cepat tersedia jika dikerjakan.

### 20.6 Admin — Could

- [ ] Impor CSV melalui halaman web menyediakan pratinjau.
- [ ] Ekspor CSV khusus admin tersedia jika dikerjakan sesuai PRD.

### 20.7 Responsivitas dan aksesibilitas

- [ ] Tata letak berfungsi pada laptop, tablet, dan ponsel.
- [ ] Peta dan panel detail tidak saling menutupi.
- [ ] Semua kontrol dapat dioperasikan dengan keyboard.
- [ ] Kontras dan ukuran teks terbaca pada tema gelap.
- [ ] Informasi tingkat risiko tidak hanya mengandalkan warna.
- [ ] Informasi peta memiliki alternatif melalui daftar/detail wilayah.

---

## 21. Panduan implementasi bertahap

Implementasikan UI mengikuti tahapan PRD:

1. **Fondasi:** layout Blade, design tokens tema navy, header, navigasi, komponen pesan/status, dan struktur halaman.
2. **Backend/data:** jangan membuat angka UI statis; sambungkan daftar kelurahan, tanggal, kelengkapan, dan status ke data sebenarnya. Gunakan `kode_wilayah` sebagai identitas wilayah.
3. **Stub prediksi:** siapkan banner simulasi, status prediksi, dan metadata model. Kategori ditampilkan dari respons layanan.
4. **Publik:** implementasikan `/peta`, status tampilan, Leaflet/GeoJSON, legenda, panel detail, dan `/tentang`. Pencarian/filter dan ringkasan mengikuti prioritas Should.
5. **Admin:** login, dashboard, daftar tanggal, form per tanggal, riwayat impor, dan aktivitas.
6. **Pengujian:** uji seluruh state, layanan Python mati, tampilan ponsel, aksesibilitas, dan larangan data statis.
7. **Integrasi model asli:** pertahankan UI; ganti metadata simulasi dengan metadata model asli; hapus banner simulasi hanya setelah model asli aktif dan hasil dihitung ulang. Tampilkan kategori sesuai respons layanan dan aturan ambang pada metadata model; jangan menanam angka ambang ke frontend.

---

## 22. Catatan konsistensi terhadap PRD

1. PRD v0.4 adalah acuan utama. UI tidak mengubah prioritas fitur, rute, kontrak API, skema database, validasi, maupun logika prediksi.
2. Pencarian/filter wilayah adalah P9 (Should), bukan Must.
3. Ringkasan jumlah wilayah per tingkat adalah P8 (Should).
4. Versi model dan waktu pembaruan adalah P7 (Should).
5. Landing page adalah P11 (Could).
6. Dashboard admin adalah A2 (Should); fungsi Must pada daftar tanggal dan halaman pengelolaan data tetap menjadi prioritas.
7. Isi cepat adalah A7 (Should).
8. Riwayat impor adalah A11 (Should).
9. Log aktivitas adalah A13 (Should).
10. Impor CSV melalui halaman web adalah A14 (Could); impor utama dilakukan lewat Artisan.
11. Ekspor CSV khusus admin adalah A15 (Could).
12. Tingkat risiko hanya `rendah`, `sedang`, dan `tinggi`.
13. Probabilitas dan tingkat ditampilkan dari hasil layanan. UI tidak menentukan atau mengubah threshold.
14. Nilai threshold, metode penetapan, dan metadata model mengikuti keputusan tim ML serta metadata `model_vN.json` sebagaimana PRD. Jika informasi belum tersedia, UI tidak boleh mengarangnya.
15. Rute penghapusan yang dirinci PRD adalah `DELETE /admin/data/{tanggal}` untuk menghapus seluruh data satu tanggal. Jangan membuat endpoint/perilaku penghapusan per baris tanpa keputusan backend yang sesuai.
16. Unduhan publik, registrasi pengguna, pelatihan model melalui website, notifikasi, aplikasi mobile, dan pengambilan data otomatis eksternal tetap di luar ruang lingkup versi ini.

**Catatan akhir:** Dokumen ini adalah spesifikasi desain UI/UX versi 1.1. Detail implementasi, aturan validasi, kontrak API, struktur database, prioritas, dan perilaku prediksi tetap mengikuti `prd.md` v0.4.
