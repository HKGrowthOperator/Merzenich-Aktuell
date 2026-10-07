<?php
/**
 * Bildherkunft und Ersatzbilder - eine Stelle fuer die ganze Seite.
 *
 * Vorher entschied jede Vorlage fuer sich, ob sie ein Beitragsbild zeigt
 * (has_post_thumbnail), und ein Credit stand nirgends. Damit konnte dasselbe
 * Bild auf der Startseite ohne und im Artikel mit Nachweis erscheinen, und ein
 * Ersatzbild war von einer echten Aufnahme nicht zu unterscheiden.
 *
 * ma_content_image() ist die einzige Stelle, die diese Frage beantwortet.
 * Vorlagen fragen nur noch ab und stellen dar.
 */
if (!defined('ABSPATH')) { exit; }

/** Erlaubte Werte fuer ma_image_type. */
const MA_IMAGE_TYPES = [
    'original'  => 'Originalbild',
    'official'  => 'Offizielles Bild',
    'licensed'  => 'Lizenziertes Bild',
    'symbol'    => 'Symbolbild',
    'place'     => 'Ortsansicht',
];

/**
 * Ersatzbild je Inhaltsart. Bewusst dieselben Grafiken wie im statischen
 * Generator (assets/img/ph-*.svg): eine gesetzte Flaeche mit Ressortnamen und
 * einer Zeile, die sagt, dass hier kein Foto vorliegt. Kein Stockfoto, kein
 * KI-Bild - beides waere an dieser Stelle eine Behauptung.
 */
function ma_image_fallback_kind($post = null): string {
    $post = get_post($post);
    if (!$post) return 'nachrichten';

    switch ($post->post_type) {
        case 'ma_property':      return 'immobilien';
        case 'ma_job':           return 'jobs';
        case 'ma_event':         return 'termine';
        case 'ma_club':          return 'vereine';
        case 'ma_business':      return 'wirtschaft';
        case 'ma_obituary':
        case 'ma_family_notice': return 'menschen';
    }

    // Beitraege: ueber die Kategorie. Feuerwehr ist eine eigene Grafik, weil
    // "Blaulicht" dort auch Polizei und Rettungsdienst meint.
    $map = [
        'feuerwehr'   => 'feuerwehr',
        'blaulicht'   => 'blaulicht',
        'polizei'     => 'blaulicht',
        'rathaus'     => 'rathaus',
        'gemeinde'    => 'rathaus',
        'politik'     => 'rathaus',
        'sport'       => 'sport',
        'vereine'     => 'vereine',
        'leben'       => 'leben',
        'menschen'    => 'menschen',
        'wirtschaft'  => 'wirtschaft',
        'termine'     => 'termine',
    ];
    foreach ((array)get_the_category($post->ID) as $cat) {
        if (isset($map[$cat->slug])) return $map[$cat->slug];
    }
    return 'nachrichten';
}

/**
 * Keine Ersatzgrafiken mehr (Betreiber 07.10.2026: „niemals wieder selber“).
 * Die selbstgezeichneten ph-*.svg sind entfernt. Ohne echtes Foto gibt es
 * keine Bild-URL; Vorlagen zeigen dann eine Karte ohne Bild. Die Funktion
 * bleibt für ältere Aufrufer und liefert immer einen leeren Text.
 */
function ma_image_fallback_url(string $kind, $post = null): string {
    return '';
}

/**
 * Kurzer Bildnachweis für Bildzeile, Karten und Feed (02.10.2026). Die
 * Rohdaten in ma_image_credit bleiben, wie sie importiert wurden; gekürzt
 * wird nur die Ausgabe: Commons-Floskeln („No machine-readable author
 * provided. X assumed (based on copyright claims).“), Wohnort („from Malmö,
 * Sweden“), Benutzerkonto („(User:H-stt)“), Diskussionslink, Web-Adressen
 * und die Langform von „(bearbeitet: …)“. Die Lizenz steht genau einmal am
 * Ende, „Public domain“ heißt „gemeinfrei“. Reine Funktion, ohne WordPress
 * testbar (qa/wordpress/images-test.php).
 */
function ma_credit_kurz(string $credit, string $lizenz = ''): string {
    $t = trim((string) preg_replace('/\s+/u', ' ', $credit));
    if ($t === '') return '';
    $t = (string) preg_replace('/^Symbolbild\s*·\s*/u', '', $t);
    $t = (string) preg_replace('/No machine-readable author provided\.\s*(.+?)\s+assumed \(based on copyright claims\)\.?/u', '$1', $t);
    $t = (string) preg_replace('/\(bearbeitet:[^)]*\)/u', '(bearbeitet)', $t);
    $t = (string) preg_replace('/\s*\(\s*Diskussion\s*\)/u', '', $t);
    $t = (string) preg_replace('/\s*\(User:[^)]*\)/u', '', $t);
    $t = (string) preg_replace('/,?\s*(?:https?:\/\/|www\.)\S+/u', '', $t);
    $t = (string) preg_replace('/ from [^\/·]+?(?= \/ Wikimedia)/u', '', $t);
    $teile = array_map('trim', preg_split('/\s*·\s*/u', $t) ?: []);
    $lz = '';
    if (count($teile) > 1 && ma_credit_ist_lizenz((string) end($teile))) $lz = (string) array_pop($teile);
    if ($lz === '' && ma_credit_ist_lizenz($lizenz)) $lz = trim($lizenz);
    $t = implode(' · ', array_filter($teile, fn($s) => $s !== ''));
    if (preg_match('/^public domain$/i', $lz)) $lz = 'gemeinfrei';
    return $lz !== '' && $t !== '' ? $t . ' · ' . $lz : ($t !== '' ? $t : $lz);
}

/** Erkennt eine Lizenzangabe (CC BY-SA 4.0, CC0, Public domain, gemeinfrei). */
function ma_credit_ist_lizenz(string $s): bool {
    return (bool) preg_match('/^(CC[ -][A-Z0-9 .\-]+|CC0(?: 1\.0)?|Public domain|gemeinfrei)$/i', trim($s));
}

/**
 * Die eine Abfrage. Liefert immer ein anzeigbares Bild - notfalls das
 * gekennzeichnete Ersatzbild.
 *
 * Reihenfolge: freigegebenes eigenes Bild, dann lizenziertes, dann Ersatz.
 *
 * @return array{url:string,type:string,type_label:string,credit:string,alt:string,
 *               license:string,source_url:string,is_fallback:bool,disclaimer:string}
 */
function ma_content_image($post = null, string $size = 'large'): array {
    $post = get_post($post);
    $id   = $post ? (int)$post->ID : 0;

    $credit     = $id ? (string)get_post_meta($id, 'ma_image_credit', true) : '';
    $license    = $id ? (string)get_post_meta($id, 'ma_image_license', true) : '';
    $source_url = $id ? (string)get_post_meta($id, 'ma_image_original_url', true) : '';
    $verified   = $id ? (string)get_post_meta($id, 'ma_image_rights_verified', true) === '1' : false;
    $type       = $id ? (string)get_post_meta($id, 'ma_image_type', true) : '';

    $thumb_id = $id ? (int)get_post_thumbnail_id($id) : 0;
    $url      = $thumb_id ? (string)get_the_post_thumbnail_url($id, $size) : '';
    $alt      = $thumb_id ? (string)get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';

    // Ein Bild zaehlt nur, wenn die Rechte geklaert sind oder eine Lizenz
    // eingetragen ist. Sonst ist es fuer die oeffentliche Seite nicht vorhanden.
    $nutzbar = $url !== '' && ($verified || $license !== '');

    if ($nutzbar) {
        if (!isset(MA_IMAGE_TYPES[$type])) {
            $type = $license !== '' && !$verified ? 'licensed' : 'original';
        }
        // Symbolbild und Ortsansicht bleiben als solche gekennzeichnet, auch
        // wenn das Foto lizenziert ist (statischer Stand: "Kein Foto vom
        // Ereignis"). Vorher wurde ein Symbolbild mit Lizenz zu "Lizenziertes
        // Bild" und verlor den Hinweis.
        $hinweis = $type === 'symbol' ? ma_image_fallback_disclaimer(ma_image_fallback_kind($post))
            : ($type === 'place' ? 'Ortsansicht. Kein Foto vom Ereignis.' : '');
        return [
            'url'         => $url,
            'type'        => $type,
            'type_label'  => MA_IMAGE_TYPES[$type],
            'credit'      => ma_credit_kurz($credit, $license),
            'alt'         => $alt !== '' ? $alt : get_the_title($id),
            'license'     => $license,
            'source_url'  => $source_url,
            'is_fallback' => false,
            'disclaimer'  => $hinweis,
        ];
    }

    $kind = ma_image_fallback_kind($post);
    return [
        'url'         => '',
        'type'        => 'symbol',
        'type_label'  => MA_IMAGE_TYPES['symbol'],
        'credit'      => $credit !== '' ? ma_credit_kurz($credit, $license) : 'Symbolbild · Merzenich Aktuell',
        'alt'         => ma_image_fallback_alt($kind),
        'license'     => $license,
        'source_url'  => '',
        'is_fallback' => true,
        'disclaimer'  => ma_image_fallback_disclaimer($kind),
    ];
}

/**
 * Die Kernregel: ein Ersatzbild darf nie so gelesen werden koennen, als zeige
 * es das angebotene Objekt, den konkreten Arbeitgeber oder den Einsatzort.
 * Deshalb sagt jede Ersatzgrafik das selbst - im Bild und in der Bildzeile.
 */
function ma_image_fallback_disclaimer(string $kind): string {
    switch ($kind) {
        case 'immobilien': return 'Symbolbild. Kein Foto des angebotenen Objekts.';
        case 'jobs':       return 'Symbolbild. Kein Foto des Arbeitgebers.';
        case 'feuerwehr':
        case 'blaulicht':  return 'Symbolbild. Nicht am Einsatzort aufgenommen.';
        case 'termine':    return 'Symbolbild. Kein Foto der Veranstaltung.';
        case 'vereine':    return 'Symbolbild. Kein Foto des Vereins.';
        case 'menschen':   return 'Symbolbild.';
        default:           return 'Symbolbild. Kein Foto zum Ereignis.';
    }
}

function ma_image_fallback_alt(string $kind): string {
    $labels = [
        'immobilien' => 'Symbolgrafik Immobilienmarkt',
        'jobs'       => 'Symbolgrafik Stellenmarkt',
        'feuerwehr'  => 'Symbolgrafik Feuerwehr',
        'blaulicht'  => 'Symbolgrafik Blaulicht',
        'rathaus'    => 'Symbolgrafik Rathaus und Politik',
        'sport'      => 'Symbolgrafik Sport',
        'vereine'    => 'Symbolgrafik Vereine',
        'termine'    => 'Symbolgrafik Veranstaltungen',
        'leben'      => 'Symbolgrafik Leben in der Gemeinde',
        'menschen'   => 'Symbolgrafik Menschen',
        'wirtschaft' => 'Symbolgrafik Wirtschaft',
    ];
    return $labels[$kind] ?? 'Symbolgrafik Merzenich Aktuell';
}

/**
 * Fertige Bildzeile fuer die Vorlagen. Ein Bild ohne Nachweis gibt es nicht:
 * entweder steht dort ein Credit oder der Ersatzhinweis.
 */
function ma_image_caption(array $bild): string {
    $teile = [];
    if ($bild['is_fallback']) {
        $teile[] = $bild['disclaimer'];
        if ($bild['credit'] !== '' && $bild['credit'] !== 'Symbolbild · Merzenich Aktuell') {
            $teile[] = $bild['credit'];
        } else {
            $teile[] = 'Merzenich Aktuell';
        }
    } else {
        if ($bild['disclaimer'] !== '')     $teile[] = $bild['disclaimer'];
        elseif ($bild['type_label'] !== '') $teile[] = $bild['type_label'] . '.';
        // Die Lizenz steckt seit 1.17.0 im gekürzten Credit (ma_credit_kurz);
        // vorher stand sie doppelt in der Bildzeile.
        if ($bild['credit'] !== '')     $teile[] = 'Foto: ' . $bild['credit'];
        elseif ($bild['license'] !== '') $teile[] = $bild['license'];
    }
    return implode(' ', array_filter($teile));
}

/** <img>-Tag mit Nachweis in einem Rutsch. */
function ma_the_content_image($post = null, string $size = 'large', array $attr = []): void {
    $bild = ma_content_image($post, $size);
    $attr = array_merge(['loading' => 'lazy', 'decoding' => 'async'], $attr);
    $html = '<img src="' . esc_url($bild['url']) . '" alt="' . esc_attr($bild['alt']) . '"';
    foreach ($attr as $k => $v) $html .= ' ' . esc_attr($k) . '="' . esc_attr((string)$v) . '"';
    $html .= $bild['is_fallback'] ? ' class="is-symbol"' : '';
    echo $html . '>';
}

/* ------------------------------------------------------------ WebP (02.10.2026)
 * Neue Bildgroessen als WebP (WordPress-Core-Filter; das Original bleibt JPEG).
 * Fuer vorhandene Anhaenge laeuft die Erzeugung gestueckelt beim Admin-Aufruf
 * nach (20 je Aufruf, Guard-Option), damit Zeitlimits des Hosters nicht reissen.
 * Kann die PHP-Installation kein WebP, passiert nichts und das Backend sagt es.
 */
function ma_webp_moeglich(): bool {
    return function_exists('wp_image_editor_supports') && wp_image_editor_supports(['mime_type' => 'image/webp']);
}
add_filter('image_editor_output_format', function (array $formate): array {
    if (ma_webp_moeglich()) $formate['image/jpeg'] = 'image/webp';
    return $formate;
});

/** Anhaenge, deren Groessen noch kein WebP sind. */
function ma_webp_offen(int $limit = 20): array {
    $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image/jpeg', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC']);
    $offen = [];
    foreach ($ids as $id) {
        $meta = wp_get_attachment_metadata($id);
        $sizes = is_array($meta) ? ($meta['sizes'] ?? []) : [];
        $hatWebp = false;
        foreach ($sizes as $sz) if (($sz['mime-type'] ?? '') === 'image/webp') { $hatWebp = true; break; }
        if (!$hatWebp) $offen[] = (int) $id;
        if (count($offen) >= $limit) break;
    }
    return $offen;
}

/* ------------------------------------------------------------ Zwischengroessen (02.10.2026)
 * WordPress liefert 300, 768, 1024, 1536 px. Vorschaubilder der Listen und
 * Buehne sind 112 bis 240 px breit; ohne Zwischengroessen lud das Handy dafuer
 * die 768er (Lighthouse „uses-responsive-images“, rund 2 MB je Seite). Die
 * neuen Groessen haengen sich von selbst ins srcset (wp_get_attachment_image_srcset).
 */
const MA_GROESSEN = ['ma-480' => 480, 'ma-240' => 240];
add_action('init', function (): void {
    foreach (MA_GROESSEN as $name => $breite) add_image_size($name, $breite, 0, false);
}, 5);

/** Anhaenge, denen eine der Zwischengroessen fehlt (nur wo das Original breiter ist). */
function ma_groessen_offen(int $limit = 20): array {
    $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => ['image/jpeg', 'image/png', 'image/webp'], 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC']);
    $offen = [];
    foreach ($ids as $id) {
        $meta = wp_get_attachment_metadata($id);
        if (!is_array($meta) || empty($meta['width'])) continue;
        $sizes = (array) ($meta['sizes'] ?? []);
        foreach (MA_GROESSEN as $name => $breite) if ((int) $meta['width'] > $breite && empty($sizes[$name])) { $offen[] = (int) $id; break; }
        if (count($offen) >= $limit) break;
    }
    return $offen;
}

/**
 * Nachlauf fuer vorhandene Anhaenge: WebP-Groessen (Guard ma_webp_fertig) und
 * Zwischengroessen (Guard ma_groessen_117), 20 je Admin-Aufruf. Beide Guards
 * fallen erst, wenn nichts mehr offen ist.
 */
function ma_bilder_nachlauf(): void {
    if (get_transient('ma_webp_lauf')) return;
    $webp = ma_webp_moeglich() && get_option('ma_webp_fertig') !== '1';
    $groessen = get_option('ma_groessen_117') !== '1';
    if (!$webp && !$groessen) return;
    set_transient('ma_webp_lauf', 1, 120);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $offen = $webp ? ma_webp_offen(20) : [];
    if ($groessen && count($offen) < 20) $offen = array_values(array_unique(array_merge($offen, ma_groessen_offen(20 - count($offen)))));
    foreach ($offen as $id) {
        $datei = get_attached_file($id);
        if (!$datei || !file_exists($datei)) continue;
        $meta = wp_generate_attachment_metadata($id, $datei);
        if (is_array($meta) && $meta) wp_update_attachment_metadata($id, $meta);
    }
    if ($webp && !ma_webp_offen(1)) update_option('ma_webp_fertig', '1', false);
    if ($groessen && !ma_groessen_offen(1)) update_option('ma_groessen_117', '1', false);
    delete_transient('ma_webp_lauf');
}
/** Alter Name (1.15.0), falls ihn etwas aufruft. */
function ma_webp_nachlauf(): void { ma_bilder_nachlauf(); }
add_action('admin_init', 'ma_bilder_nachlauf', 20);
