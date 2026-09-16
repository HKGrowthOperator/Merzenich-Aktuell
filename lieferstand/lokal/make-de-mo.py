#!/usr/bin/env python3
"""
Erzeugt einen kleinen deutschen Sprachkatalog (Wochentage, Monate) fuer die
lokale Vorschau. Auf einer echten Installation laedt WordPress unter
Einstellungen -> Allgemein das vollstaendige Sprachpaket von wordpress.org;
diese Datei ersetzt es nur, solange kein Netzzugang zu wordpress.org besteht.
"""
import struct, sys, os

days = [('Monday','Montag','Mon','Mo','M'),('Tuesday','Dienstag','Tue','Di','T'),
        ('Wednesday','Mittwoch','Wed','Mi','W'),('Thursday','Donnerstag','Thu','Do','T'),
        ('Friday','Freitag','Fri','Fr','F'),('Saturday','Samstag','Sat','Sa','S'),
        ('Sunday','Sonntag','Sun','So','S')]
months = [('January','Januar','Jan','Jan'),('February','Februar','Feb','Feb'),
          ('March','März','Mar','Mrz'),('April','April','Apr','Apr'),
          ('May','Mai','May','Mai'),('June','Juni','Jun','Jun'),
          ('July','Juli','Jul','Jul'),('August','August','Aug','Aug'),
          ('September','September','Sep','Sep'),('October','Oktober','Oct','Okt'),
          ('November','November','Nov','Nov'),('December','Dezember','Dec','Dez')]

e = {'': ("Project-Id-Version: WordPress\n"
          "Language: de_DE\n"
          "MIME-Version: 1.0\n"
          "Content-Type: text/plain; charset=UTF-8\n"
          "Content-Transfer-Encoding: 8bit\n"
          "Plural-Forms: nplurals=2; plural=(n != 1);\n")}
# WordPress verwendet die Kurzformen je nach Version mit oder ohne Kontext.
# Beide Schreibweisen eintragen, damit der Katalog in beiden Faellen greift.
for en, de, ab_en, ab_de, ini in days:
    e[en] = de
    e[ab_en] = ab_de
    e['%s abbreviation\x04%s' % (en, ab_en)] = ab_de
    e['%s initial\x04%s' % (en, ini)] = de[0]
for en, de, ab_en, ab_de in months:
    e[en] = de
    if ab_en != de:
        e[ab_en] = ab_de
    e['%s abbreviation\x04%s' % (en, ab_en)] = ab_de
    e['genitive\x04%s' % en] = de

keys = sorted(e)
n = len(keys)
orig_tab_off  = 28
trans_tab_off = orig_tab_off + n * 8
strings_off   = trans_tab_off + n * 8

orig_tab, trans_tab, blob = b'', b'', b''
off = strings_off
for k in keys:
    b_ = k.encode('utf-8')
    orig_tab += struct.pack('<II', len(b_), off)
    blob += b_ + b'\x00'
    off += len(b_) + 1
for k in keys:
    b_ = e[k].encode('utf-8')
    trans_tab += struct.pack('<II', len(b_), off)
    blob += b_ + b'\x00'
    off += len(b_) + 1

header = struct.pack('<IIIIIII', 0x950412de, 0, n, orig_tab_off, trans_tab_off, 0, 0)
dest = sys.argv[1] if len(sys.argv) > 1 else 'wp/wp-content/languages/de_DE.mo'
os.makedirs(os.path.dirname(dest), exist_ok=True)
open(dest, 'wb').write(header + orig_tab + trans_tab + blob)
print('%s geschrieben, %d Eintraege' % (dest, n))
