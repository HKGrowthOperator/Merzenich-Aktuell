# Merzenich Aktuell – WordPress einrichten und aktualisieren

Stand 7. Oktober 2026 · Theme 21.13.0 · Core-Plugin 1.22.0 · live auf merzenich-aktuell.de (IONOS, WordPress 7.1)

Die Bedienung für die Redaktion steht in `UEBERGABE-KBS.md`, die Änderungen je Version in `WORDPRESS-LIEFERUNG.md`. Die frühere Fassung dieser Anleitung (Theme 20.2, September 2026) ist überholt.

## Bestandteile

| Paket | Inhalt |
|---|---|
| `merzenich-aktuell-core.zip` (Plugin) | Inhaltstypen (Termine, Vereine, Märkte, Anzeigen, Betriebe, Werbung, Eingang), Freigaben, Abgleich, Sport, Formulare, Kommentar-Moderation, SEO und Google News, Rechtstexte und Redaktionsseiten, Push, Statistik |
| `merzenich-aktuell-theme.zip` (Theme) | Darstellung wie die frühere statische Seite: Kopf, Fuß und Startseite aus `vorlagen/`, Stile und Bilder unter `static/` (ausgeliefert als `/assets/…`), Seitenvorlagen für alle Inhaltstypen |

Gebaut werden beide aus dem Repository mit `node deploy/wp-pakete.mjs` (legt die ZIPs in `wordpress-delivery/` ab; das Theme bekommt dabei `chatgpt-site/assets` als `static/`).

## Voraussetzungen

- PHP 8.1 oder neuer (empfohlen 8.3), 256 MB Speicher, 32 MB Upload.
- HTTPS mit gültigem Zertifikat. Das Theme leitet `http://` mit 301 auf `https://` um und setzt HSTS.
- Apache mit `.htaccess` (IONOS): Das Theme schreibt einen eigenen Block für `/assets/` und sperrt `readme.html`, `license.txt` und `wp-config-sample.php`. Nach jedem Theme-Update schreibt es die Regeln einmal neu.
- Einstellungen → Allgemein: Sprache Deutsch, Zeitzone **Europe/Berlin**.
- Permalinks `/%category%/%postname%/`, Schlagwort-Basis `thema` (setzt das Plugin bei leerer Einstellung selbst).

## Aktualisieren (Theme oder Plugin)

1. Vorher sichern: Datenbank und `wp-content` (IONOS-Backup oder Werkzeuge → Daten exportieren).
2. **Design → Themes → Theme hinzufügen → Theme hochladen** bzw. **Plugins → Plugin hinzufügen → Plugin hochladen**, ZIP wählen, „Aktuelle Version durch hochgeladene ersetzen“ bestätigen.
3. Danach prüfen: Startseite, eine Meldung, `/termine/`, `/sport/`, ein Formular. Die Version steht im Quelltext jeder Seite bei `plattform.css?ver=…`.

Theme und Plugin gehören zusammen: In `WORDPRESS-LIEFERUNG.md` steht je Version, ob beide hochgeladen werden müssen.

## Automatik und Zeitplan

- **WP-Cron** läuft bei Seitenaufrufen. Ein echter Server-Cron (IONOS: Cronjob alle 5 Minuten auf `https://merzenich-aktuell.de/wp-cron.php`) macht geplante Beiträge, Abgleich, ablaufende Anzeigen und Teilen-Zuschnitte pünktlicher.
- **Abgleich:** stündlich, zusätzlich angestoßen vom GitHub-Workflow „WordPress-Abgleich anstoßen“. Neue Meldungen und Termine kommen als Entwurf in die Freigaben.
- **Teilen-Zuschnitte:** Für jedes Beitragsbild entstehen einmal Zuschnitte in 16:9, 4:3 und 1:1 (`…-teilen-16x9.jpg` usw.), für vorhandene Bilder nach und nach über WP-Cron.

## Mailversand

Formulare, Partner-Anträge und Kommentar-Benachrichtigungen nutzen `wp_mail`. Für verlässliche Zustellung SMTP über das IONOS-Postfach einrichten (etwa mit einem SMTP-Plugin) und SPF/DKIM/DMARC der Domain prüfen. Empfänger der Formulare: Feld „Redaktions-E-Mail“ auf der Seite **Merzenich Aktuell**, leer gilt die Admin-E-Mail. Einsendungen landen unabhängig vom Mailversand immer im **Eingang**.

## Prüfen nach einer Änderung

Im Repository:

```bash
for t in qa/wordpress/*-test.php; do php "$t" || echo "FEHLER: $t"; done
node deploy/kette.mjs --check
```

Lokale WordPress-Testumgebung, Live-Prüfung (Vollprüfung aller Seiten, Darstellung auf Handy und Computer) und die Liste der offenen Betreiberaufgaben: siehe `WORDPRESS-LIEFERUNG.md`.
