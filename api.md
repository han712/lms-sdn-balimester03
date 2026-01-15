# API Documentation — Model Prediction (Flask)

## Overview ✅
This service exposes a small Flask API that predicts *risiko remedial* based on student metrics.

- Server: Flask app located at `storage/app/python/flask.py`
- Model files: `model_sdn03.pkl` and `scaler_sdn03.pkl` (place next to the script or set `MODEL_DIR`)

---

## Endpoints 🔧

### GET /health
- Purpose: check service and model load status
- Request: GET http://43.134.27.73:5000/health
- Response (200 OK when loaded):

```json
{ "status": "ok", "model_loaded": true, "message": null }
```

- Response (503 Service Unavailable when model not loaded):

```json
{ "status": "ok", "model_loaded": false, "message": "Model file not found: /path/to/model_sdn03.pkl" }
```

---

### POST /predict
- Purpose: return prediction for a single student input
- URL: POST http://43.134.27.73:5000/predict
- Content-Type: `application/json` (also accepts form-data / query params)

#### Accepted input fields (JSON keys):
- `rata_kuis` or `rata` or `rata_nilai_kuis` (float) — average quiz score
- `total_kuis` or `total_kuis_dikerjakan` (int) — total quizzes taken
- `kehadiran` or `presentasi_kehadiran` or `kehadiran_percent` (float) — attendance percentage (0-100)
- `tidak_hadir` or `jumlah_tidak_hadir` or `absent` (int) — number of absences

#### Example request (JSON):

```bash
curl -X POST http://43.134.27.73:5000/predict \
  -H "Content-Type: application/json" \
  -d '{"rata_kuis": 75, "total_kuis": 5, "kehadiran": 95, "tidak_hadir": 0}'
```

#### Successful response (200 OK):

```json
{
  "prediksi": 1,
  "label": "BERISIKO REMEDIAL",
  "confidence": "92.34"
}
```

- If the model does not implement `predict_proba`, `confidence` may be `null`.

#### Common error responses
- 400 Bad Request — invalid or missing input:
```json
{ "status": "error", "message": "Invalid input types: could not convert string to float" }
```
- 503 Service Unavailable — model not loaded:
```json
{ "status": "error", "message": "Model not loaded: Model file not found: /path/to/model_sdn03.pkl" }
```
- 500 Internal Server Error — unexpected failure during prediction:
```json
{ "status": "error", "message": "Prediction failed: <details>" }
```

---

## Run instructions ⚙️

1. Ensure dependencies are installed (preferably inside a venv):

```bash
pip install -r storage/app/python/req_sd.txt
# or at minimum: pip install flask pandas numpy scikit-learn
```

2. Place model files next to the script, or set `MODEL_DIR` environment variable:

- Example (PowerShell):

```powershell
$env:MODEL_DIR = "C:\path\to\models"
python storage/app/python/flask.py
```

3. Start server:

```bash
python storage/app/python/predict_api.py
# listens on http://43.134.27.73:5000
```

---

## Troubleshooting & Notes ⚠️

- If loading fails with a Windows-specific error involving `asyncio` / `WinError 10106`: try
  1. Run `netsh winsock reset` as Administrator and reboot, or
  2. Run the service inside WSL or Docker, or
  3. Reinstall/repair Python (use official installer or use Conda/Miniconda)

- If you prefer to avoid unpickling scikit-learn objects on Windows entirely, consider converting the model to ONNX or exporting weights/numpy arrays and implementing a lightweight inference path.

- For production use, run Flask with a production WSGI server (Gunicorn/uvicorn via ASGI wrapper or use Docker + a process manager). If you want, I can provide a Dockerfile.

---

## Examples (Python requests) 🐍

```python
import requests
url = 'http://43.134.27.73:5000/predict'
payload = {"rata_kuis": 75, "total_kuis": 5, "kehadiran": 95, "tidak_hadir": 0}
r = requests.post(url, json=payload)
print(r.status_code, r.json())
```

---

If you'd like, I can also:
- Add a Postman collection or OpenAPI spec, ✅
- Add an automated downloader for the model file if a URL is available, or
- Provide a Dockerfile + compose file to run the service reliably on Windows.

Tell me which of these you'd like next.
