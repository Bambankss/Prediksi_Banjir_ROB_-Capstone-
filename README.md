# Website Prediksi Banjir ROB Jakarta Utara

Proyek Capstone Machine Learning (tanpa anggaran): Website visualisasi dan pengelolaan prediksi risiko banjir pasang air laut (ROB) untuk **31 kelurahan di Kota Administrasi Jakarta Utara**.

---

## 1. Arsitektur & Lingkungan

- **Backend & Frontend Web:** Laravel 11 + SQLite + Blade + Tailwind CSS v4 + Alpine.js + Leaflet.
- **Lingkungan Pengembangan:** Laragon (Windows), PHP 8.3 dengan ekstensi `pdo_sqlite` dan `sqlite3`.
- **Layanan Prediksi ML:** Microservice Python (FastAPI) di `http://127.0.0.1:8001`, stateless, dipanggil melalui HTTP dengan header `X-Service-Token`.
- **Model Machine Learning Aktif:** **Random Forest Classifier (`model-v1`)** dengan akurasi 80.5%, Recall 85.0%, dan ROC-AUC 86.25%. Menggunakan 5 fitur masukan: `elevasi, jarak_pantai, curah_hujan, pasang_tertinggi, gelombang_tertinggi`.

---

## 2. Cara Menjalankan Proyek Setelah Clone dari GitHub

Jika Anda meng-clone proyek ini dari repositori GitHub, jalankan langkah inisialisasi berikut:

### Langkah 1: Setup Website Laravel
```bash
cd laravel-app

# 1. Pasang dependensi PHP dan JavaScript
composer install
npm install
npm run build

# 2. Siapkan file konfigurasi lingkungan (.env)
cp .env.example .env
php artisan key:generate

# 3. Inisialisasi basis data SQLite dan seeder kelurahan
# (Di Windows PowerShell gunakan: New-Item database/database.sqlite -ItemType File)
touch database/database.sqlite
php artisan migrate --seed

# 4. Impor data harian historis (8.463 baris data 2026)
php artisan import:data-harian ../data/data_harian_2026.csv

# 5. Jalankan web server Laravel
php artisan serve
```
Website publik dapat diakses melalui browser di `http://127.0.0.1:8000`.

### Langkah 2: Setup & Jalankan Layanan Prediksi ML (FastAPI)
Buka terminal baru di folder proyek:
```bash
# 1. Pasang dependensi Python yang dibutuhkan
pip install -r ml-service/requirements.txt scikit-learn joblib

# 2. Jalankan microservice (atau klik ganda berkas jalankan-ml-service.bat)
jalankan-ml-service.bat
```
Layanan microservice akan aktif di `http://127.0.0.1:8001`.

### Langkah 3: Hitung Prediksi Seluruh Tanggal
Setelah layanan FastAPI berjalan, jalankan perintah ini di terminal `laravel-app`:
```bash
php artisan prediksi:hitung
```
Seluruh data prediksi untuk 273 hari akan langsung terisi dan siap divisualisasikan pada peta interaktif.

---

## 3. Cara Menjalankan di Laragon (Alternatif)

### Langkah 1: Jalankan Web Server
1. Buka aplikasi **Laragon** di Windows.
2. Klik tombol **Start All** (Apache/Nginx aktif). Virtual host otomatis diarahkan ke `http://rob-predictor.test` atau `http://localhost/capstone/laravel-app/public`.
3. Alternatif menggunakan terminal bawaan di folder `laravel-app`:
   ```bash
   cd C:\laragon\www\capstone\laravel-app
   php artisan serve
   ```
   Website dapat diakses di `http://127.0.0.1:8000`.

### Langkah 2: Jalankan Layanan Prediksi Python
Klik ganda berkas batch di root proyek:
```
jalankan-ml-service.bat
```
Atau jalankan melalui terminal:
```bash
cd C:\laragon\www\capstone\ml-service
uvicorn app:app --host 127.0.0.1 --port 8001 --reload
```
Layanan akan mendengarkan di `http://127.0.0.1:8001`.

---

## 3. Akun Administrator Default

- **URL Login Admin:** `http://127.0.0.1:8000/admin/login` (atau `http://rob-predictor.test/admin/login`)
- **Email:** `admin@rob.test`
- **Kata Sandi:** `password123`

Untuk membuat admin baru melalui terminal:
```bash
cd C:\laragon\www\capstone\laravel-app
php artisan admin:buat
```

---

## 4. Perintah Artisan Khusus

Semua perintah dijalankan dari folder `laravel-app`:

1. **Validasi GeoJSON 31 Kelurahan:**
   ```bash
   php artisan geojson:validasi
   ```
2. **Impor Data Harian CSV (All-or-Nothing):**
   ```bash
   php artisan import:data-harian ../data_harian_2026.csv --sumber="BMKG 2026"
   ```
3. **Hitung Ulang Prediksi (Massal):**
   ```bash
   php artisan prediksi:hitung
   # Atau untuk tanggal tertentu:
   php artisan prediksi:hitung --tanggal=2026-01-01 --force
   ```

---

## 5. Menjalankan Pengujian Otomatis

- **Pengujian Aplikasi Laravel (21 Tests, 264 Assertions):**
  ```bash
  cd C:\laragon\www\capstone\laravel-app
  php artisan test
  ```
- **Pengujian Layanan Python FastAPI (4 Tests):**
  ```bash
  cd C:\laragon\www\capstone\ml-service
  python -m pytest
  ```

---

## 6. Halaman dan Rute Utama

| Rute | Fungsi | Akses |
|---|---|---|
| `/peta` | Peta interaktif kerentanan banjir ROB 31 kelurahan | Publik |
| `/tentang` | Halaman Tentang Data, sumber, metode, dan disclaimer | Publik |
| `/admin/login` | Halaman masuk administrator | Publik |
| `/admin` | Dashboard admin (status data, model, layanan, dan audit) | Admin (Auth) |
| `/admin/data` | Daftar tanggal, kelengkapan n/31, dan status prediksi | Admin (Auth) |
| `/admin/data/{tanggal}` | Formulir edit 31 kelurahan + alat bantu isi cepat | Admin (Auth) |
| `/admin/impor` | Riwayat dan rincian audit impor data | Admin (Auth) |
| `/admin/aktivitas` | Log audit aktivitas admin | Admin (Auth) |

