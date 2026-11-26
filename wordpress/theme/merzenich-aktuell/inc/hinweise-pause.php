<?php
/**
 * Bildhinweise vorübergehend aus (Entscheidung des Betreibers, 26.11.2026, für den Start).
 *
 * Bis MA21_HINWEISE_PAUSE_BIS (Ortszeit Berlin) blendet die öffentliche Seite die Marken
 * auf Bildern aus („Symbolbild“, „Ortsansicht“, „Bild: Quelle“, „Archivbild“) und die
 * Hinweise in Bildunterschriften („Symbolbild ·“, „Kein Foto vom Ereignis.“, „Kein Foto der
 * Veranstaltung.“, KI-Hinweise in Bildunterschrift und Bildnachweis). Danach greift nichts mehr; alles erscheint von selbst wieder.
 * Die Anzeigen-Kennzeichnung („Anzeige“) bleibt immer unberührt. Backend, REST und Feeds bleiben unverändert.
 */
if (!defined('ABSPATH')) { exit; }

const MA21_HINWEISE_PAUSE_BIS = '2026-11-28 04:00:00';

function ma21_hinweise_pause(?int $jetzt = null): bool {
    $bis = strtotime(MA21_HINWEISE_PAUSE_BIS . ' Europe/Berlin');
    return $bis !== false && ($jetzt ?? time()) < $bis;
}

/** Entfernt Bildmarken und Hinweise aus dem fertigen HTML einer öffentlichen Seite. */
function ma21_hinweise_entfernen(string $html): string {
    $html = (string) preg_replace('#<span class="badge">[^<]*</span>#u', '', $html);
    $html = (string) preg_replace('#<span class="figure-badge">[^<]*</span>\s*·\s*#u', '', $html);
    $html = (string) preg_replace('#<span class="ressort-dropdown__muster ressort-dropdown__symbol">[^<]*</span>#u', '', $html);
    // KI-Hinweise in Bildunterschrift und Bildnachweis (Titelbild Glühweinabend 27.11.2026).
    $html = (string) preg_replace('#(<figcaption><span>)KI-generiertes Stimmungsbild mit den Eckdaten: #u', '$1', $html);
    $html = str_replace('Bild: KI-Bild; ', 'Bild: ', $html);
    return str_replace(['. Kein Foto vom Ereignis.', ' Kein Foto vom Ereignis.', 'Kein Foto der Veranstaltung. ', ' Kein Foto der Veranstaltung.'], ['.', '', '', ''], $html);
}

add_action('template_redirect', function (): void {
    if (is_admin() || wp_doing_ajax() || is_feed() || (defined('REST_REQUEST') && REST_REQUEST) || !ma21_hinweise_pause()) return;
    ob_start('ma21_hinweise_entfernen');
}, 0);

// Marken, die Skripte erst im Browser setzen (Bild-Ersatz, Ressort-Menü), ebenfalls ausblenden.
add_action('wp_head', function (): void {
    if (is_admin() || !ma21_hinweise_pause()) return;
    echo '<style id="ma-hinweise-pause">.media .badge,.markt-thumb__badge,.figure-badge,.ressort-dropdown__symbol{display:none!important}</style>' . "\n";
}, 99);
