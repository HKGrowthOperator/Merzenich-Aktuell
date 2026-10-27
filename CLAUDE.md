# Merzenich Aktuell

Lies zuerst PROJECT.md. Für Redaktion, neue Meldungen und Bilder danach docs/redaktion/README.md, docs/redaktion/latest.md und docs/redaktion/register.json lesen.

Die Dossiers sind Recherche und Arbeitsaufträge, keine Publikationsfreigabe. Vor Umsetzung Quellen, Datenstand, Dubletten, Rechte und Freigabestatus prüfen. Bearbeitete Themen mit Artikelpfad und Commit im Register dokumentieren. Bestehende Architektur und Quellenimporte nutzen.

Für den zweimal täglichen Claude-Redaktionslauf ist docs/redaktion/CLAUDE-LAUF.md die verbindliche Laufdefinition (Phasen, Abnahmen, Störungen, was ohne Freigabe sichtbar wird). Dazu docs/redaktion/SCHREIBREGELN.md (Artikeltyp, Verifikation, zwei Quellen, Datenschutz), docs/redaktion/QUELLEN.md (Quellenkatalog, Sperren, Umwege), docs/redaktion/REGISTER-SCHEMA.md und die Vorlagen unter docs/redaktion/vorlagen/. Vor jedem Commit eines Laufs: node qa/redaktion-lauf.mjs (0 Fehler). Claude-Termine immer mit "entwurf": true.
