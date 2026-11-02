import os
import sys
import pytest

sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))

from fastapi.testclient import TestClient
from app import app, SERVICE_TOKEN

client = TestClient(app)

HEADERS_VALID = {"X-Service-Token": SERVICE_TOKEN}
HEADERS_INVALID = {"X-Service-Token": "wrong-token"}


def test_health_unauthorized():
    response = client.get("/health", headers=HEADERS_INVALID)
    assert response.status_code == 401
    assert response.json()["kode"] == "TOKEN_TIDAK_VALID"


def test_health_success():
    response = client.get("/health", headers=HEADERS_VALID)
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "ok"
    assert data["model_versi"] == "stub-v0"
    assert "elevasi" in data["fitur"]


def test_predict_success():
    payload = {
        "rows": [
            {
                "kode_wilayah": "31.72.01.1001",
                "elevasi": 0.4,
                "jarak_pantai": 0.35,
                "curah_hujan": 12.5,
                "pasang_tertinggi": 1.2,
                "gelombang_tertinggi": 0.9,
            },
            {
                "kode_wilayah": "31.72.01.1002",
                "elevasi": 3.5,
                "jarak_pantai": 4.5,
                "curah_hujan": 0.0,
                "pasang_tertinggi": 0.4,
                "gelombang_tertinggi": 0.2,
            },
        ]
    }
    response = client.post("/predict", json=payload, headers=HEADERS_VALID)
    assert response.status_code == 200
    data = response.json()
    assert data["model_versi"] == "stub-v0"
    assert len(data["hasil"]) == 2
    assert data["hasil"][0]["kode_wilayah"] == "31.72.01.1001"
    assert 0.0 <= data["hasil"][0]["probabilitas"] <= 1.0
    assert data["hasil"][0]["tingkat"] in ["rendah", "sedang", "tinggi"]


def test_predict_duplicate_codes():
    payload = {
        "rows": [
            {
                "kode_wilayah": "31.72.01.1001",
                "elevasi": 0.4,
                "jarak_pantai": 0.35,
                "curah_hujan": 12.5,
                "pasang_tertinggi": 1.2,
                "gelombang_tertinggi": 0.9,
            },
            {
                "kode_wilayah": "31.72.01.1001",
                "elevasi": 0.4,
                "jarak_pantai": 0.35,
                "curah_hujan": 12.5,
                "pasang_tertinggi": 1.2,
                "gelombang_tertinggi": 0.9,
            },
        ]
    }
    response = client.post("/predict", json=payload, headers=HEADERS_VALID)
    assert response.status_code == 422
    assert response.json()["kode"] == "KODE_DUPLIKAT"


def test_predict_model_mode(monkeypatch):
    monkeypatch.setattr("app.PREDICTOR_MODE", "model")
    monkeypatch.setattr("app.MODEL_VERSI", "model-v1")

    payload = {
        "rows": [
            {
                "kode_wilayah": "31.72.01.1001",
                "elevasi": 0.4,
                "jarak_pantai": 0.35,
                "curah_hujan": 12.5,
                "pasang_tertinggi": 1.2,
                "gelombang_tertinggi": 0.9,
            },
            {
                "kode_wilayah": "31.72.01.1002",
                "elevasi": 3.5,
                "jarak_pantai": 4.5,
                "curah_hujan": 0.0,
                "pasang_tertinggi": 0.4,
                "gelombang_tertinggi": 0.2,
            },
        ]
    }
    response = client.post("/predict", json=payload, headers=HEADERS_VALID)
    assert response.status_code == 200
    data = response.json()
    assert data["model_versi"] == "model-v1"
    assert data["mode"] == "model"
    assert data["simulasi"] is False
    assert len(data["hasil"]) == 2
    assert 0.0 <= data["hasil"][0]["probabilitas"] <= 1.0
    assert data["hasil"][0]["tingkat"] in ["rendah", "sedang", "tinggi"]
