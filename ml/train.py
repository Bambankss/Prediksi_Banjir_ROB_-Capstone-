"""
Skrip pelatihan Machine Learning untuk Prediksi Banjir ROB Jakarta Utara.
Sesuai PRD v0.4 Bagian 5 dan 17 (Tahap 7).
Algoritma: Random Forest Classifier (dengan Baseline Logistic Regression).
"""

import os
import json
import datetime
import numpy as np
import pandas as pd
import joblib

from sklearn.model_selection import GroupShuffleSplit
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler
from sklearn.linear_model import LogisticRegression
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import (
    accuracy_score,
    precision_score,
    recall_score,
    f1_score,
    roc_auc_score,
    confusion_matrix,
    classification_report
)

def train_model():
    print("==================================================")
    print("      PELATIHAN MODEL PREDIKSI BANJIR ROB        ")
    print("==================================================")

    # 1. Memuat Dataset
    path_data = None
    pilihan_path = [
        os.path.join('data', 'data_latih.csv'),
        os.path.join('data', 'dataset_latih.csv'),
        'data_latih.csv',
        'dataset_latih.csv'
    ]
    for p in pilihan_path:
        if os.path.exists(p):
            path_data = p
            break

    if not path_data:
        raise FileNotFoundError("Berkas dataset latih tidak ditemukan!")

    print(f"[1/6] Membaca dataset dari: {path_data}")
    df = pd.read_csv(path_data, dtype={'kode_wilayah': str})
    print(f"      Total baris: {len(df)}")
    print(f"      Rentang tanggal: {df['tanggal'].min()} s/d {df['tanggal'].max()}")
    print(f"      Distribusi kelas is_banjir: {dict(df['is_banjir'].value_counts())}")

    # Kontrak Fitur Sesuai PRD Bagian 5.1
    features = ['elevasi', 'jarak_pantai', 'curah_hujan', 'pasang_tertinggi', 'gelombang_tertinggi']
    target = 'is_banjir'

    # 2. Pembagian Data Berdasarkan Tanggal (PRD 5.7)
    print("\n[2/6] Membagi data train & test berbasis tanggal (GroupShuffleSplit 80:20)...")
    gss = GroupShuffleSplit(n_splits=1, test_size=0.20, random_state=42)
    train_idx, test_idx = next(gss.split(df, groups=df['tanggal']))

    train_df = df.iloc[train_idx].copy()
    test_df = df.iloc[test_idx].copy()

    X_train, y_train = train_df[features], train_df[target]
    X_test, y_test = test_df[features], test_df[target]

    print(f"      Data latih : {len(X_train)} baris (Banjir: {(y_train == 1).sum()}, Non-banjir: {(y_train == 0).sum()})")
    print(f"      Data uji   : {len(X_test)} baris (Banjir: {(y_test == 1).sum()}, Non-banjir: {(y_test == 0).sum()})")

    # 3. Model Baseline: Logistic Regression
    print("\n[3/6] Melatih Model Baseline (Logistic Regression)...")
    lr_pipe = Pipeline([
        ('scaler', StandardScaler()),
        ('clf', LogisticRegression(random_state=42))
    ])
    lr_pipe.fit(X_train, y_train)
    y_pred_lr = lr_pipe.predict(X_test)
    y_prob_lr = lr_pipe.predict_proba(X_test)[:, 1]

    acc_lr = accuracy_score(y_test, y_pred_lr)
    rec_lr = recall_score(y_test, y_pred_lr)
    f1_lr = f1_score(y_test, y_pred_lr)
    auc_lr = roc_auc_score(y_test, y_prob_lr)
    print(f"      [Baseline LR] Akurasi: {acc_lr:.4f} | Recall: {rec_lr:.4f} | F1: {f1_lr:.4f} | ROC-AUC: {auc_lr:.4f}")

    # 4. Model Utama: Random Forest Classifier
    print("\n[4/6] Melatih Model Utama (Random Forest Classifier)...")
    rf_clf = RandomForestClassifier(
        n_estimators=100,
        max_depth=6,
        min_samples_split=4,
        min_samples_leaf=2,
        class_weight='balanced',
        random_state=42
    )

    rf_pipe = Pipeline([
        ('scaler', StandardScaler()),
        ('clf', rf_clf)
    ])

    rf_pipe.fit(X_train, y_train)
    y_pred_rf = rf_pipe.predict(X_test)
    y_prob_rf = rf_pipe.predict_proba(X_test)[:, 1]

    acc_rf = accuracy_score(y_test, y_pred_rf)
    prec_rf = precision_score(y_test, y_pred_rf)
    rec_rf = recall_score(y_test, y_pred_rf)
    f1_rf = f1_score(y_test, y_pred_rf)
    auc_rf = roc_auc_score(y_test, y_prob_rf)
    cm_rf = confusion_matrix(y_test, y_pred_rf)

    print("      --- Hasil Evaluasi Data Uji (Random Forest) ---")
    print(f"      Akurasi          : {acc_rf:.4f} ({acc_rf*100:.1f}%)")
    print(f"      Presisi (Kelas 1): {prec_rf:.4f}")
    print(f"      Recall (Kelas 1) : {rec_rf:.4f} ({rec_rf*100:.1f}%)")
    print(f"      F1-Score         : {f1_rf:.4f}")
    print(f"      ROC-AUC          : {auc_rf:.4f}")
    print(f"      Confusion Matrix :\n{cm_rf}")

    # Feature Importance
    trained_rf = rf_pipe.named_steps['clf']
    feat_importances = dict(zip(features, [round(float(v), 4) for v in trained_rf.feature_importances_]))
    print("\n      Tingkat Pengaruh Fitur (Feature Importance):")
    for k, v in sorted(feat_importances.items(), key=lambda item: item[1], reverse=True):
        print(f"       - {k:<20}: {v*100:.2f}%")

    # 5. Fit Model Akhir pada Seluruh Data (Production Fit)
    print("\n[5/6] Mempersiapkan Pipeline Produksi Akhir...")
    final_pipeline = Pipeline([
        ('scaler', StandardScaler()),
        ('clf', RandomForestClassifier(
            n_estimators=100,
            max_depth=6,
            min_samples_split=4,
            min_samples_leaf=2,
            class_weight='balanced',
            random_state=42
        ))
    ])
    final_pipeline.fit(df[features], df[target])

    # 6. Ekspor Artefak Model (PRD 5.2)
    os.makedirs('ml-service/model_artifacts', exist_ok=True)
    joblib_path = os.path.join('ml-service', 'model_artifacts', 'model_v1.joblib')
    json_path = os.path.join('ml-service', 'model_artifacts', 'model_v1.json')

    joblib.dump(final_pipeline, joblib_path)
    print(f"\n[6/6] Artefak model tersimpan:")
    print(f"      - {joblib_path} (Ukuran: {os.path.getsize(joblib_path) / 1024:.1f} KB)")

    metadata = {
        "model_versi": "model-v1",
        "algoritma": "RandomForestClassifier",
        "tanggal_latih": datetime.date.today().isoformat(),
        "fitur": features,
        "ambang_tingkat": {
            "tinggi": 0.65,
            "sedang": 0.35,
            "rendah": 0.0
        },
        "metrik_uji": {
            "akurasi": round(float(acc_rf), 4),
            "precision_kelas_1": round(float(prec_rf), 4),
            "recall_kelas_1": round(float(rec_rf), 4),
            "f1_kelas_1": round(float(f1_rf), 4),
            "roc_auc": round(float(auc_rf), 4)
        },
        "baseline_logistic_regression": {
            "akurasi": round(float(acc_lr), 4),
            "recall_kelas_1": round(float(rec_lr), 4),
            "f1_kelas_1": round(float(f1_lr), 4),
            "roc_auc": round(float(auc_lr), 4)
        },
        "feature_importance": feat_importances,
        "periode_data": {
            "awal": df['tanggal'].min(),
            "akhir": df['tanggal'].max(),
            "total_baris": len(df)
        }
    }

    with open(json_path, 'w', encoding='utf-8') as f:
        json.dump(metadata, f, indent=2, ensure_ascii=False)
    print(f"      - {json_path}")

    print("\n[SUKSES] PELATIHAN MODEL SELESAI DENGAN SUKSES!")
    return metadata

if __name__ == '__main__':
    train_model()
