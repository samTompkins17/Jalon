"""Génère les captures publiques de la vitrine à partir des maquettes originales.

- les originales restent dans Maquette/ (hors public/, et ignorées par git) ;
- sortie : public/img/captures/*.webp, largeur max 900 px, WebP qualité 60,
  métadonnées supprimées, filigrane discret « Jalon · aperçu » ;
- les anciennes captures qui ne font plus partie de la sélection sont effacées.

Usage : python tools/generer-apercus-vitrine.py
"""

from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

RACINE = Path(__file__).resolve().parent.parent
ORIGINALES = RACINE / "Maquette" / "extrait" / "Jalon_maquette"
SORTIE = RACINE / "public" / "img" / "captures"

LARGEUR_MAX = 900
QUALITE = 60
FILIGRANE = "Jalon · aperçu"

# La vitrine ne montre que des écrans de pilotage : ni paramètres, ni équipe,
# ni permissions.
ECRANS = [
    ("tableau-de-bord", "Tableau de bord - Thème clair.jpg"),
    ("fiche-dossier", "Fiche dossier - Thème clair.jpg"),
    ("passage-de-jalon-modale", "Passage de jalon (modale) - Thème clair.jpg"),
    ("conteneurs", "Conteneurs - Thème clair.jpg"),
    ("alertes", "Alertes - Thème clair.jpg"),
    ("facturation", "Facturation - Thème clair.jpg"),
    ("espace-client-mobile", "Espace client mobile - Thème clair.jpg"),
    ("suivi-par-code-mobile", "Suivi par code mobile - Thème clair.jpg"),
]


def police(taille: int) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    for nom in ("segoeui.ttf", "arial.ttf", "DejaVuSans.ttf"):
        try:
            return ImageFont.truetype(nom, taille)
        except OSError:
            continue

    try:
        return ImageFont.load_default(size=taille)
    except TypeError:  # Pillow < 10
        return ImageFont.load_default()


def filigraner(image: Image.Image) -> None:
    """Filigrane discret en bas à droite : pastille sombre translucide."""
    dessin = ImageDraw.Draw(image, "RGBA")
    taille = max(12, image.width // 58)
    texte = police(taille)
    marge = max(14, image.width // 64)

    boite = dessin.textbbox((0, 0), FILIGRANE, font=texte)
    largeur = boite[2] - boite[0]
    hauteur = boite[3] - boite[1]
    remplissage_x, remplissage_y = taille * 0.7, taille * 0.45

    droite = image.width - marge
    bas = image.height - marge
    gauche = droite - largeur - remplissage_x * 2
    haut = bas - hauteur - remplissage_y * 2

    dessin.rounded_rectangle(
        (gauche, haut, droite, bas),
        radius=(bas - haut) / 2,
        fill=(17, 16, 15, 105),
    )
    dessin.text(
        (gauche + remplissage_x, haut + remplissage_y - boite[1]),
        FILIGRANE,
        font=texte,
        fill=(255, 255, 255, 190),
    )


def generer(slug: str, originale: Path) -> Path:
    with Image.open(originale) as source:
        image = source.convert("RGB")

    if image.width > LARGEUR_MAX:
        hauteur = round(image.height * LARGEUR_MAX / image.width)
        image = image.resize((LARGEUR_MAX, hauteur), Image.LANCZOS)

    filigraner(image)

    SORTIE.mkdir(parents=True, exist_ok=True)
    cible = SORTIE / f"{slug}.webp"
    image.save(cible, format="WEBP", quality=QUALITE, method=6, exif=b"")

    return cible


def main() -> int:
    if not ORIGINALES.is_dir():
        print(f"Maquettes introuvables : {ORIGINALES}")
        return 1

    gardees = set()
    total = 0

    for slug, nom in ECRANS:
        originale = ORIGINALES / nom

        if not originale.is_file():
            print(f"ABSENTE : {nom}")
            return 1

        cible = generer(slug, originale)
        gardees.add(cible.name)
        total += cible.stat().st_size
        print(f"{cible.name:38} {cible.stat().st_size // 1024:>4} Ko")

    for ancienne in SORTIE.glob("*.webp"):
        if ancienne.name not in gardees:
            ancienne.unlink()
            print(f"retirée : {ancienne.name}")

    print(f"{len(gardees)} captures publiques, {total // 1024} Ko au total.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
