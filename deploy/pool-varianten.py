#!/usr/bin/env python3
"""
Merzenich Aktuell - WebP-Varianten fuer die Commons-Poolfotos.

Die Poolfotos liegen als JPG bis 1600 px Breite vor (250-900 KB). In Kacheln
von 200-400 px Breite laedt der Browser sonst die volle Datei. Dieses Skript
legt neben jedes Foto WebP-Fassungen in 480, 800 und 1200 px Breite:

    assets/editorial-pools/<pool>/<name>.jpg
    assets/editorial-pools/<pool>/<name>-480.webp   (usw.)

deploy/symbolbilder.mjs schreibt daraus das srcset der Artikelbilder, der
Inhaltsindex uebernimmt es fuer Startseite und Listen.

Idempotent: vorhandene Varianten bleiben unangetastet. Varianten, deren
Ausgangsfoto nicht mehr existiert (vom Importer ausgeschlossen), werden
geloescht.

Aufruf:  python3 deploy/pool-varianten.py [--check]
--check: nichts schreiben, Exitcode 1, wenn Varianten fehlen oder verwaist sind.
"""
import re
import sys
from pathlib import Path

BREITEN = (480, 800, 1200)
WURZEL = Path(__file__).resolve().parent.parent / 'chatgpt-site' / 'assets' / 'editorial-pools'
VARIANTE = re.compile(r'^(?P<basis>.+)-(?P<breite>\d{3,4})\.webp$')
PRUEFEN = '--check' in sys.argv


def main() -> int:
    try:
        from PIL import Image
    except ImportError:
        Image = None
    fehlend, verwaist, neu = [], [], 0
    for ordner in sorted(p for p in WURZEL.iterdir() if p.is_dir()):
        fotos = {f.stem: f for f in ordner.iterdir() if f.suffix.lower() in ('.jpg', '.jpeg', '.png') and not VARIANTE.match(f.name)}
        for f in ordner.glob('*.webp'):
            m = VARIANTE.match(f.name)
            if m and m['basis'] not in fotos:
                verwaist.append(f)
        for stamm, foto in sorted(fotos.items()):
            breite = None
            for b in BREITEN:
                ziel = ordner / f'{stamm}-{b}.webp'
                if ziel.exists():
                    continue
                if breite is None:
                    if Image is None:
                        fehlend.append(ziel)
                        continue
                    with Image.open(foto) as im:
                        breite = im.width
                if breite is not None and b >= breite:
                    continue  # kein Hochskalieren; das Original deckt diese Breite
                fehlend.append(ziel)
                if PRUEFEN or Image is None:
                    continue
                with Image.open(foto) as im:
                    im = im.convert('RGB')
                    h = round(im.height * b / im.width)
                    im.resize((b, h), Image.LANCZOS).save(ziel, 'WEBP', quality=76, method=6)
                neu += 1
    if PRUEFEN:
        for f in fehlend:
            print('fehlt:', f.relative_to(WURZEL.parent.parent))
        for f in verwaist:
            print('verwaist:', f.relative_to(WURZEL.parent.parent))
        return 1 if (fehlend or verwaist) else 0
    if Image is None and fehlend:
        print('Pillow fehlt: python3 -m pip install pillow', file=sys.stderr)
        return 2
    for f in verwaist:
        f.unlink()
    print(f'Poolfoto-Varianten: {neu} neu, {len(verwaist)} verwaiste entfernt.')
    return 0


if __name__ == '__main__':
    sys.exit(main())
