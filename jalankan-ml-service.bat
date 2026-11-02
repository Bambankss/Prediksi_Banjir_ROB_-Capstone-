@echo off
title Layanan Prediksi ROB (FastAPI)
echo Menjalankan layanan prediksi ROB di http://127.0.0.1:8001 ...
cd /d %~dp0ml-service
python -m uvicorn app:app --host 127.0.0.1 --port 8001 --reload
pause

