"""
Pemeriksa dataset latih untuk proyek Prediksi Banjir ROB Jakarta Utara.
Sesuai PRD v0.4 Bagian 5.7.
"""

import sys
import os
import re
import pandas as pd

def periksa_dataset(path_latih=None, path_kelurahan=None):
    # Cari berkas latih
    if path_latih is None:
        pilihan = [
            os.path.join('data', 'data_latih.csv'),
            os.path.join('data', 'dataset_latih.csv'),
            'dataset_latih.csv',
            'data_latih.csv'
        ]
        for p in pilihan:
            if os.path.exists(p):
                path_latih = p
                break
    
    # Cari berkas kelurahan
    if path_kelurahan is None:
        pilihan_k = [
            os.path.join('data', 'kelurahan.csv'),
            'kelurahan.csv'
        ]
        for p in pilihan_k:
            if os.path.exists(p):
                path_kelurahan = p
                break

    print(f"=== Pemeriksaan Dataset Latih ===")
    print(f"File latih: {path_latih}")
    print(f"File kelurahan: {path_kelurahan}\n")

    if not path_latih or not os.path.exists(path_latih):
        print(f"[GAGAL] Berkas dataset latih tidak ditemukan.")
        return False
    if not path_kelurahan or not os.path.exists(path_kelurahan):
        print(f"[GAGAL] Berkas kelurahan.csv tidak ditemukan.")
        return False

    df_latih = pd.read_csv(path_latih, dtype={'kode_wilayah': str})
    df_kel = pd.read_csv(path_kelurahan, dtype={'kode_wilayah': str})

    semua_lolos = True
    peringatan = []

    # 1. Periksa kolom wajib
    kolom_wajib = ['tanggal', 'kode_wilayah', 'kelurahan', 'elevasi', 'jarak_pantai', 'curah_hujan', 'pasang_tertinggi', 'gelombang_tertinggi', 'is_banjir']
    kolom_hilang = [k for k in kolom_wajib if k not in df_latih.columns]
    if kolom_hilang:
        print(f"[GAGAL] Kolom wajib hilang: {kolom_hilang}")
        return False
    else:
        print(f"[OK] Semua kolom wajib ada ({len(kolom_wajib)} kolom).")

    # 2. Periksa format tanggal ISO (YYYY-MM-DD)
    iso_pattern = re.compile(r'^\d{4}-\d{2}-\d{2}$')
    non_iso = df_latih[~df_latih['tanggal'].astype(str).str.match(iso_pattern)]
    if len(non_iso) > 0:
        print(f"[GAGAL] Terdapat {len(non_iso)} baris dengan tanggal tidak berformat YYYY-MM-DD.")
        semua_lolos = False
    else:
        min_tgl = df_latih['tanggal'].min()
        max_tgl = df_latih['tanggal'].max()
        print(f"[OK] Format tanggal 100% ISO YYYY-MM-DD (Rentang: {min_tgl} s/d {max_tgl}).")

    # 3. Periksa format kode_wilayah NN.NN.NN.NNNN dan kecocokan dengan kelurahan.csv
    kode_pattern = re.compile(r'^\d{2}\.\d{2}\.\d{2}\.\d{4}$')
    kode_invalid = df_latih[~df_latih['kode_wilayah'].astype(str).str.match(kode_pattern)]
    if len(kode_invalid) > 0:
        print(f"[GAGAL] Terdapat {len(kode_invalid)} baris dengan kode wilayah tidak valid.")
        semua_lolos = False
    else:
        print(f"[OK] Seluruh kode_wilayah berformat Kemendagri NN.NN.NN.NNNN.")

    kel_resmi = set(df_kel['kode_wilayah'].astype(str).unique())
    kel_latih = set(df_latih['kode_wilayah'].astype(str).unique())
    kel_asing = kel_latih - kel_resmi
    if kel_asing:
        print(f"[GAGAL] Terdapat kode_wilayah asing yang tidak terdaftar di kelurahan.csv: {kel_asing}")
        semua_lolos = False
    else:
        print(f"[OK] Seluruh kode_wilayah di dataset latih valid dan terdaftar resmi.")

    # 4. Periksa duplikat (tanggal, kode_wilayah)
    duplikat = df_latih[df_latih.duplicated(subset=['tanggal', 'kode_wilayah'], keep=False)]
    if len(duplikat) > 0:
        print(f"[GAGAL] Terdapat {len(duplikat)} baris duplikat untuk pasangan (tanggal, kode_wilayah).")
        semua_lolos = False
    else:
        print(f"[OK] Tidak ada duplikasi kombinasi (tanggal, kode_wilayah).")

    # 5. Periksa nilai kosong (missing values)
    null_counts = df_latih[kolom_wajib].isnull().sum()
    total_null = null_counts.sum()
    if total_null > 0:
        print(f"[GAGAL] Terdapat nilai kosong:\n{null_counts[null_counts > 0]}")
        semua_lolos = False
    else:
        print(f"[OK] Tidak ada nilai kosong (0 null).")

    # 6. Periksa label is_banjir (harus hanya 0 atau 1)
    nilai_label = set(df_latih['is_banjir'].unique())
    if not nilai_label.issubset({0, 1}):
        print(f"[GAGAL] Label is_banjir mengandung nilai di luar 0 dan 1: {nilai_label}")
        semua_lolos = False
    else:
        jml_0 = (df_latih['is_banjir'] == 0).sum()
        jml_1 = (df_latih['is_banjir'] == 1).sum()
        print(f"[OK] Nilai is_banjir valid: 0={jml_0} baris, 1={jml_1} baris (Total: {len(df_latih)} baris).")

    # 7. Periksa batas kewajaran nilai fitur
    # curah_hujan >= 0
    if (df_latih['curah_hujan'] < 0).any():
        print(f"[GAGAL] Terdapat curah_hujan negatif.")
        semua_lolos = False
    # gelombang_tertinggi >= 0
    if (df_latih['gelombang_tertinggi'] < 0).any():
        print(f"[GAGAL] Terdapat gelombang_tertinggi negatif.")
        semua_lolos = False
    # jarak_pantai dalam km (biasanya 0 s/d 25 km untuk Jakarta Utara)
    if (df_latih['jarak_pantai'] > 30).any():
        peringatan.append("Beberapa jarak_pantai > 30 km; pastikan satuan dalam km bukan meter.")
    else:
        print(f"[OK] Satuan fitur masuk akal (curah_hujan >= 0, gelombang >= 0, jarak_pantai dalam km).")

    # 8. Periksa sebaran kelurahan
    jml_kel_terwakili = len(kel_latih)
    if jml_kel_terwakili < 31:
        peringatan.append(
            f"Dataset latih hanya mencakup {jml_kel_terwakili} dari 31 kelurahan. "
            f"26 kelurahan lainnya akan diprediksi menggunakan generalisasi fitur elevasi dan jarak pantai."
        )
    else:
        print(f"[OK] Seluruh 31 kelurahan terwakili dalam data latih.")

    print("\n--- Ringkasan Pemeriksaan ---")
    if peringatan:
        print("[PERINGATAN]:")
        for p in peringatan:
            print(f" - {p}")

    if semua_lolos:
        print("\n HASIL: DATASET VALID DAN SIAP DIGUNAKAN UNTUK TRAINING.")
        return True
    else:
        print("\n❌ HASIL: TERDAPAT KESALAHAN PADA DATASET.")
        return False

if __name__ == '__main__':
    sukses = periksa_dataset()
    sys.exit(0 if sukses else 1)

