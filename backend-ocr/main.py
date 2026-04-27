from fastapi import FastAPI, UploadFile, File, Form
import shutil
import uuid
import os

from ocr import extract_text
from ocr_pdf import extract_text_pdf
from parser.parser_shopee import parse_shopee
from parser.parser_tiktok import parse_tiktok
from ocr_tiktok import extract_tiktok_from_pdf, extract_tiktok_from_image


app = FastAPI()

UPLOAD_DIR = "uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)

ALLOWED_EXT = ["jpg", "jpeg", "png", "pdf"]


@app.post("/scan-resi")
async def scan_resi(
    mode: str = Form(...),
    file: UploadFile = File(...)
):

    # ambil extension
    ext = file.filename.split(".")[-1].lower()

    # validasi format file
    if ext not in ALLOWED_EXT:
        return {
            "error": "Format file tidak didukung. Gunakan PDF/JPG/PNG"
        }

    filename = f"{UPLOAD_DIR}/{uuid.uuid4()}.{ext}"

    with open(filename, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)


    # di dalam scan_resi:
    if ext == "pdf":
        if mode == "tiktok":
            text, items = extract_tiktok_from_pdf(filename)
        else:
            text, items = extract_text_pdf(filename)  # shopee
    else:
        if mode == "tiktok":
            text, items = extract_tiktok_from_image(filename)
        else:
            text, items = extract_text(filename)  # shopee

    print("OCR TEXT:")
    print(text)

    if mode == "shopee":
        result = parse_shopee(text, items)  # pass items langsung
    elif mode == "tiktok":
        result = parse_tiktok(text, items)
    else:
        result = {"error": "mode tidak dikenali"}
    return {
        "mode": mode,
        "result": result
    }