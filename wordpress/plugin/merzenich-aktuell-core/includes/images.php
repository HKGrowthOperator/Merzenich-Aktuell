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
 * Ortsteile duerfen eigene Ersatzbilder haben. Existiert keines, bleibt es bei
 * der Ressortgrafik - ein fremder Ortsteil waere schlechter als gar keiner.
 */
function ma_image_fallback_url(string $kind, $post = null): string {
    $base = trailingslashit(get_template_directory_uri()) . 'assets/img/';

    $terms = $post ? get_the_terms(get_post($post), 'ma_location') : [];
    if (is_array($terms)) {
        foreach ($terms as $t) {
            $ort = $base . 'ph-ort-' . $t->slug . '.svg';
            $datei = trailingslashit(get_template_directory()) . 'assets/img/ph-ort-' . $t->slug . '.svg';
            if (file_exists($datei)) return $ort;
        }
    }
    return $base . 'ph-' . $kind . '.svg';
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
            'credit'      => $credit,
            'alt'         => $alt !== '' ? $alt : get_the_title($id),
            'license'     => $license,
            'source_url'  => $source_url,
            'is_fallback' => false,
            'disclaimer'  => $hinweis,
        ];
    }

    $kind = ma_image_fallback_kind($post);
    return [
        'url'         => ma_image_fallback_url($kind, $post),
        'type'        => 'symbol',
        'type_label'  => MA_IMAGE_TYPES['symbol'],
        'credit'      => $credit !== '' ? $credit : 'Symbolbild · Merzenich Aktuell',
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
        if ($bild['credit'] !== '')     $teile[] = 'Foto: ' . $bild['credit'];
        if ($bild['license'] !== '')    $teile[] = $bild['license'];
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

function ma_webp_nachlauf(): void {
    if (!ma_webp_moeglich() || get_option('ma_webp_fertig') === '1' || get_transient('ma_webp_lauf')) return;
    set_transient('ma_webp_lauf', 1, 120);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $offen = ma_webp_offen(20);
    foreach ($offen as $id) {
        $datei = get_attached_file($id);
        if (!$datei || !file_exists($datei)) continue;
        $meta = wp_generate_attachment_metadata($id, $datei);
        if (is_array($meta) && $meta) wp_update_attachment_metadata($id, $meta);
    }
    if (count($offen) < 20) update_option('ma_webp_fertig', '1', false);
    delete_transient('ma_webp_lauf');
}
add_action('admin_init', 'ma_webp_nachlauf', 20);
