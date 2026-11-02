import os
import math
import json
from typing import List, Optional
from fastapi import FastAPI, Header, HTTPException, Request, status
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field, field_validator
import pandas as pd
import joblib

app = FastAPI(title="ROB Predictor Service", version="1.0.0")

SERVICE_TOKEN = os.getenv("PREDICTOR_TOKEN", "rahasia-stub-token-123")
PREDICTOR_MODE = os.getenv("PREDICTOR_MODE", "stub")
MODEL_VERSI = os.getenv("MODEL_VERSI", "stub-v0")
FITUR_LIST = ["elevasi", "jarak_pantai", "curah_hujan", "pasang_tertinggi", "gelombang_tertinggi"]

# Load model pipeline & metadata if in model mode or available
MODEL_PIPELINE = None
MODEL_METADATA = None

ARTIFACTS_DIR = os.path.join(os.path.dirname(__file__), "model_artifacts")
MODEL_JOBLIB_PATH = os.path.join(ARTIFACTS_DIR, "model_v1.joblib")
MODEL_JSON_PATH = os.path.join(ARTIFACTS_DIR, "model_v1.json")

if os.path.exists(MODEL_JOBLIB_PATH) and os.path.exists(MODEL_JSON_PATH):
    try:
        MODEL_PIPELINE = joblib.load(MODEL_JOBLIB_PATH)
        with open(MODEL_JSON_PATH, "r", encoding="utf-8") as f:
            MODEL_METADATA = json.load(f)
    except Exception as e:
        print(f"Peringatan: Gagal memuat artefak model ML: {e}")


class FeatureRow(BaseModel):
    kode_wilayah: str = Field(..., description="Kode wilayah Kemendagri format NN.NN.NN.NNNN")
    elevasi: float = Field(..., description="Ketinggian tanah (m)")
    jarak_pantai: float = Field(..., description="Jarak ke garis pantai (km)")
    curah_hujan: float = Field(..., ge=0, description="Curah hujan (mm/hari)")
    pasang_tertinggi: float = Field(..., description="Tinggi pasang air laut maksimum (m)")
    gelombang_tertinggi: float = Field(..., ge=0, description="Tinggi gelombang laut maksimum (m)")

    @field_validator("kode_wilayah")
    @classmethod
    def validate_kode(cls, v: str) -> str:
        v = v.strip()
        if not v:
            raise ValueError("kode_wilayah tidak boleh kosong")
        return v


class PredictRequest(BaseModel):
    rows: List[FeatureRow]


def check_auth(x_service_token: Optional[str] = Header(None, alias="X-Service-Token")):
    if not x_service_token or x_service_token != SERVICE_TOKEN:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"status": "error", "kode": "TOKEN_TIDAK_VALID", "pesan": "Token layanan tidak valid atau tidak disertakan."},
        )


@app.exception_handler(HTTPException)
async def custom_http_exception_handler(request: Request, exc: HTTPException):
    if isinstance(exc.detail, dict):
        return JSONResponse(status_code=exc.status_code, content=exc.detail)
    return JSONResponse(
        status_code=exc.status_code,
        content={"status": "error", "kode": "GALAT_LAYANAN", "pesan": str(exc.detail)},
    )


@app.get("/health")
def health(x_service_token: Optional[str] = Header(None, alias="X-Service-Token")):
    check_auth(x_service_token)
    is_simulasi = PREDICTOR_MODE == "stub" or MODEL_VERSI.startswith("stub") or MODEL_PIPELINE is None
    fitur = MODEL_METADATA.get("fitur", FITUR_LIST) if MODEL_METADATA else FITUR_LIST
    return {
        "status": "ok",
        "mode": PREDICTOR_MODE,
        "simulasi": is_simulasi,
        "model_versi": MODEL_VERSI,
        "fitur": fitur,
    }


def compute_stub_probability(row: FeatureRow) -> float:
    # Heuristik simulasi banjir ROB:
    pasang_component = max(0.0, row.pasang_tertinggi) * 0.40
    elevasi_component = max(0.0, 3.0 - max(0.0, row.elevasi)) * 0.12
    jarak_component = max(0.0, 3.0 - min(3.0, row.jarak_pantai)) * 0.12
    hujan_component = min(50.0, max(0.0, row.curah_hujan)) / 50.0 * 0.20
    gelombang_component = min(2.0, max(0.0, row.gelombang_tertinggi)) * 0.16

    raw_score = pasang_component + elevasi_component + jarak_component + hujan_component + gelombang_component
    prob = 1.0 / (1.0 + math.exp(-3.2 * (raw_score - 0.95)))
    return max(0.02, min(0.98, prob))


@app.post("/predict")
def predict(payload: PredictRequest, x_service_token: Optional[str] = Header(None, alias="X-Service-Token")):
    check_auth(x_service_token)

    if not payload.rows:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail={"status": "error", "kode": "DATA_KOSONG", "pesan": "Daftar baris fitur tidak boleh kosong."},
        )

    # Validasi kode duplikat
    codes = [r.kode_wilayah for r in payload.rows]
    if len(codes) != len(set(codes)):
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail={"status": "error", "kode": "KODE_DUPLIKAT", "pesan": "Terdapat kode_wilayah ganda dalam permintaan."},
        )

    is_simulasi = PREDICTOR_MODE == "stub" or MODEL_VERSI.startswith("stub") or MODEL_PIPELINE is None

    hasil = []

    if not is_simulasi and MODEL_PIPELINE is not None:
        # Gunakan model Random Forest asli
        fitur_cols = MODEL_METADATA.get("fitur", FITUR_LIST) if MODEL_METADATA else FITUR_LIST
        ambang = MODEL_METADATA.get("ambang_tingkat", {"tinggi": 0.65, "sedang": 0.35, "rendah": 0.0}) if MODEL_METADATA else {"tinggi": 0.65, "sedang": 0.35, "rendah": 0.0}

        # Format input dataframe
        data_dicts = []
        for r in payload.rows:
            data_dicts.append({
                "elevasi": r.elevasi,
                "jarak_pantai": r.jarak_pantai,
                "curah_hujan": r.curah_hujan,
                "pasang_tertinggi": r.pasang_tertinggi,
                "gelombang_tertinggi": r.gelombang_tertinggi,
            })
        
        df_input = pd.DataFrame(data_dicts)[fitur_cols]
        # Prediksi probabilitas kelas 1 (ROB)
        probs = MODEL_PIPELINE.predict_proba(df_input)[:, 1]

        for r, prob in zip(payload.rows, probs):
            prob_val = float(prob)
            if prob_val >= ambang.get("tinggi", 0.65):
                tingkat = "tinggi"
            elif prob_val >= ambang.get("sedang", 0.35):
                tingkat = "sedang"
            else:
                tingkat = "rendah"

            hasil.append({
                "kode_wilayah": r.kode_wilayah,
                "probabilitas": round(prob_val, 4),
                "tingkat": tingkat,
            })
    else:
        # Gunakan mode simulasi stub
        for r in payload.rows:
            prob = compute_stub_probability(r)
            if prob >= 0.70:
                tingkat = "tinggi"
            elif prob >= 0.40:
                tingkat = "sedang"
            else:
                tingkat = "rendah"

            hasil.append({
                "kode_wilayah": r.kode_wilayah,
                "probabilitas": round(prob, 4),
                "tingkat": tingkat,
            })

    return {
        "model_versi": MODEL_VERSI,
        "mode": PREDICTOR_MODE,
        "simulasi": is_simulasi,
        "hasil": hasil,
    }
