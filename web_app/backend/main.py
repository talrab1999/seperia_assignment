from pathlib import Path

from fastapi import FastAPI
from fastapi.staticfiles import StaticFiles

from .catalog import router as catalog_router


PROJECT_ROOT = Path(__file__).resolve().parents[1]
FRONTEND_DIR = PROJECT_ROOT / "frontend"
STATIC_DIR = FRONTEND_DIR / "static"

app = FastAPI(title="E-Commerce Product Shop")
app.mount("/static", StaticFiles(directory=STATIC_DIR), name="static")
app.include_router(catalog_router)
