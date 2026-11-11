import fitz  # PyMuPDF
import os
from pathlib import Path

# Répertoire des maquettes
maquettes_dir = Path(r"C:\Users\samto\Desktop\Projet_soutenance\JALON\maquettes")

# Convertir tous les PDFs en PNG
for pdf_file in maquettes_dir.glob("*.pdf"):
    try:
        doc = fitz.open(pdf_file)
        for page_num in range(len(doc)):
            page = doc[page_num]
            pix = page.get_pixmap(matrix=fitz.Matrix(80/72, 80/72))  # 80 DPI
            output_file = pdf_file.stem
            if len(doc) > 1:
                output_file += f"-{page_num + 1}"
            output_file += ".png"
            pix.save(maquettes_dir / output_file)
        doc.close()
        print(f"Converti: {pdf_file.name}")
    except Exception as e:
        print(f"Erreur pour {pdf_file.name}: {e}")

print("Conversion terminée")
