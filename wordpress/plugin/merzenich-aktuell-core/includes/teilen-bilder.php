<?php
/**
 * Teilen-Zuschnitte (1.22.0): Google empfiehlt für Artikel und Discover mehrere
 * Bilder in 16:9, 4:3 und 1:1, möglichst 1200 px breit und mindestens 50 000
 * Bildpunkte groß. WhatsApp, Facebook und Co. zeigen 16:9 am saubersten.
 *
 * Je Beitragsbild entstehen die drei Zuschnitte einmal, mittig aus dem Bild
 * geschnitten und nie hochgerechnet: beim Speichern eines veröffentlichten
 * Beitrags und für vorhandene Bilder nach und nach über WP-Cron (20 je Lauf).
 * Sie liegen als JPEG neben dem Bild (…-teilen-16x9.jpg) und stehen in der
 * Metaangabe ma_zuschnitte, nicht in den WordPress-Größen: eine Neuberechnung
 * der Größen (WebP-Nachlauf) verliert sie so nicht, und das srcset der Seite
 * bleibt unverändert. ma_seo_bild() (seo.php) nimmt sie für og:image und
 * JSON-LD, sobald sie da sind; vorher bleibt alles wie bisher.
 */
if (!defined('ABSPATH')) { exit; }

const MA_ZUSCHNITTE = ['16x9' => [16, 9], '4x3' => [4, 3], '1x1' => [1, 1]];
const MA_ZUSCHNITT_BREITE = 1200;
const MA_ZUSCHNITT_TYPEN = ['post', 'ma_event', 'ma_club', 'ma_job', 'ma_property', 'ma_tip', 'ma_business'];
const MA_ZUSCHNITT_CRON = 'ma_zuschnitte_nachlauf';

/**
 * Mittiger Ausschnitt im Seitenverhältnis $rw:$rh aus einem Bild $w×$h und die
 * Zielmaße (höchstens 1200 px breit, nie größer als der Ausschnitt). null, wenn
 * das Ergebnis unter Googles 50 000 Bildpunkten bliebe.
 */
function ma_seo_zuschnitt_masse(int $w, int $h, int $rw, int $rh): ?array {
    if ($w < 1 || $h < 1 || $rw < 1 || $rh < 1) return null;
    if ($w * $rh > $h * $rw) { $ch = $h; $cw = (int) floor($h * $rw / $rh); }
    else { $cw = $w; $ch = (int) floor($w * $rh / $rw); }
    $zw = min(MA_ZUSCHNITT_BREITE, $cw);
    $zh = (int) round($zw * $rh / $rw);
    if ($zw * $zh < 50000) return null;
    return ['x' => intdiv($w - $cw, 2), 'y' => intdiv($h - $ch, 2), 'w' => $cw, 'h' => $ch, 'zw' => $zw, 'zh' => $zh];
}

/** Fertige Zuschnitte eines Anhangs als [name => [url, w, h]] (nur vorhandene Dateien aus der Metaangabe). */
function ma_seo_zuschnitte(int $id): array {
    $z = get_post_meta($id, 'ma_zuschnitte', true);
    if (!is_array($z)) return [];
    $basis = dirname((string) wp_get_attachment_url($id));
    $aus = [];
    foreach (array_keys(MA_ZUSCHNITTE) as $name) {
        if (!empty($z[$name]['file'])) $aus[$name] = ['url' => $basis . '/' . rawurlencode((string) $z[$name]['file']), 'w' => (int) $z[$name]['w'], 'h' => (int) $z[$name]['h']];
    }
    return $aus;
}

/** Erzeugt fehlende Zuschnitte eines Anhangs. Nicht mögliche (Bild zu klein) werden vermerkt und nicht erneut versucht. */
function ma_seo_zuschnitte_erzeugen(int $id): void {
    $alt = get_post_meta($id, 'ma_zuschnitte', true);
    $alt = is_array($alt) ? $alt : [];
    $datei = (string) get_attached_file($id);
    $groesse = $datei !== '' && is_file($datei) ? @getimagesize($datei) : false;
    if (!$groesse) { update_post_meta($id, 'ma_zuschnitte', ['fehlt' => true]); return; }
    $ordner = dirname($datei);
    $name0 = pathinfo($datei, PATHINFO_FILENAME);
    $neu = $alt;
    unset($neu['fehlt']);
    // JPEG, nicht WebP (images.php stellt Uploads auf WebP um): nicht jeder Messenger zeigt WebP in der Vorschau.
    $nur_jpeg = static function (array $f): array { unset($f['image/jpeg']); return $f; };
    add_filter('image_editor_output_format', $nur_jpeg, PHP_INT_MAX);
    foreach (MA_ZUSCHNITTE as $name => [$rw, $rh]) {
        if (isset($neu[$name]) && ($neu[$name]['file'] === '' || is_file($ordner . '/' . $neu[$name]['file']))) continue;
        $m = ma_seo_zuschnitt_masse((int) $groesse[0], (int) $groesse[1], $rw, $rh);
        if (!$m) { $neu[$name] = ['file' => '', 'w' => 0, 'h' => 0]; continue; }
        $ed = wp_get_image_editor($datei);
        if (is_wp_error($ed)) { $neu[$name] = ['file' => '', 'w' => 0, 'h' => 0]; continue; }
        $ed->set_quality(82);
        $erg = $ed->crop($m['x'], $m['y'], $m['w'], $m['h'], $m['zw'], $m['zh']);
        if (!is_wp_error($erg)) $erg = $ed->save($ordner . '/' . $name0 . '-teilen-' . $name . '.jpg', 'image/jpeg');
        $neu[$name] = is_wp_error($erg) ? ['file' => '', 'w' => 0, 'h' => 0] : ['file' => basename((string) $erg['path']), 'w' => (int) $erg['width'], 'h' => (int) $erg['height']];
    }
    remove_filter('image_editor_output_format', $nur_jpeg, PHP_INT_MAX);
    if ($neu !== $alt) update_post_meta($id, 'ma_zuschnitte', $neu);
}

/** Beitragsbilder veröffentlichter Inhalte, denen die Zuschnitte noch fehlen. */
function ma_seo_zuschnitte_offen(int $limit = 20): array {
    $posts = get_posts(['post_type' => MA_ZUSCHNITT_TYPEN, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC',
        'meta_query' => [['key' => '_thumbnail_id', 'compare' => 'EXISTS']], 'no_found_rows' => true]);
    $offen = [];
    foreach ($posts as $p) {
        $bild = (int) get_post_thumbnail_id($p);
        if ($bild && !in_array($bild, $offen, true) && !is_array(get_post_meta($bild, 'ma_zuschnitte', true))) $offen[] = $bild;
        if (count($offen) >= $limit) break;
    }
    return $offen;
}

/* Neu veröffentlicht oder geändert: Zuschnitte gleich anlegen (Freigabe, Editor, Abgleich). */
add_action('wp_after_insert_post', function (int $id, WP_Post $p): void {
    if ($p->post_status !== 'publish' || !in_array($p->post_type, MA_ZUSCHNITT_TYPEN, true)) return;
    $bild = (int) get_post_thumbnail_id($p);
    if ($bild) ma_seo_zuschnitte_erzeugen($bild);
}, 20, 2);

/* Vorhandene Bilder: 20 je Cron-Lauf, danach in einer Minute weiter, bis nichts mehr offen ist. */
add_action(MA_ZUSCHNITT_CRON, function (): void {
    if (get_transient('ma_zuschnitte_lauf')) return;
    set_transient('ma_zuschnitte_lauf', 1, 5 * MINUTE_IN_SECONDS);
    foreach (ma_seo_zuschnitte_offen(20) as $id) ma_seo_zuschnitte_erzeugen($id);
    delete_transient('ma_zuschnitte_lauf');
    if (ma_seo_zuschnitte_offen(1)) wp_schedule_single_event(time() + MINUTE_IN_SECONDS, MA_ZUSCHNITT_CRON);
    else update_option('ma_zuschnitte_fertig', MA_CORE_VERSION, false);
});
add_action('init', function (): void {
    if (get_option('ma_zuschnitte_fertig') === MA_CORE_VERSION || wp_next_scheduled(MA_ZUSCHNITT_CRON)) return;
    wp_schedule_single_event(time() + 30, MA_ZUSCHNITT_CRON);
});

/* Gelöschter Anhang: Zuschnitte mit entfernen. */
add_action('delete_attachment', function (int $id): void {
    $z = get_post_meta($id, 'ma_zuschnitte', true);
    $datei = (string) get_attached_file($id);
    if (!is_array($z) || $datei === '') return;
    foreach (array_keys(MA_ZUSCHNITTE) as $name) {
        if (!empty($z[$name]['file'])) wp_delete_file(dirname($datei) . '/' . $z[$name]['file']);
    }
});
