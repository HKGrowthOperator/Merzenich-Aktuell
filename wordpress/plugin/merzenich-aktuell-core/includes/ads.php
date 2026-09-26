<?php
/**
 * Werbung: Plaetze, aktive Anzeigen, Rotation.
 *
 * Entscheidung KBS 26.09.2026: Anzeigen duerfen sich wiederholen, und mehrere
 * verschiedene Anzeigen rotieren auf einem Platz. ma_active_ads() liefert
 * deshalb ALLE laufenden, freigegebenen Anzeigen eines Platzes; die Ausgabe
 * legt sie gemeinsam in einen Container, ein kleines Skript wechselt sie.
 * ma_active_ad() bleibt fuer bestehende Aufrufer erhalten und liefert die
 * erste davon (hoechste Prioritaet, dann die neueste).
 *
 * Preise werden nirgends ausgegeben (Vorgabe: immer "Preis auf Anfrage").
 */
if (!defined('ABSPATH')) { exit; }

/** Werbebaender der Startseite: eines zwischen jeder Rubrik. */
function ma_ad_band_slots(): array {
    return ['homepage_band_1','homepage_band_2','homepage_band_3','homepage_band_4','homepage_band_5','homepage_band_6'];
}

function ma_ad_slots(): array {
    return array_merge(
        ['homepage_sidebar_top','homepage_sidebar_middle'],
        ma_ad_band_slots(),
        ['homepage_tip','homepage_feed_1','article_inline_1','article_sidebar','header_billboard']
    );
}

/** Lesbare Namen fuer Verwaltung, Kontingent und Hinweise. */
function ma_ad_slot_label(string $slot): string {
    $labels = [
        'homepage_sidebar_top' => 'Startseite – rechte Spalte oben',
        'homepage_sidebar_middle' => 'Startseite – rechte Spalte Mitte',
        'homepage_tip' => 'Startseite – Tipp · Sponsoring',
        'homepage_feed_1' => 'Startseite – Ende der Meldungsliste',
        'article_inline_1' => 'Artikel – im Text',
        'article_sidebar' => 'Artikel – Seitenspalte',
        'header_billboard' => 'Kopfbereich – Billboard',
    ];
    if (preg_match('/^homepage_band_(\d+)$/', $slot, $m)) return 'Startseite – Werbeband '.$m[1];
    return $labels[$slot] ?? $slot;
}

function ma_register_ads_hooks(): void {
    add_shortcode('ma_ad', 'ma_ad_shortcode');
    add_action('wp_enqueue_scripts', 'ma_ads_register_assets');
}

function ma_ads_register_assets(): void {
    wp_register_script('ma-ad-rotation', MA_CORE_URL.'assets/ad-rotation.js', [], MA_CORE_VERSION, true);
}

/** Zeitstempel "jetzt" im Format der gespeicherten Start-/Endwerte. */
function ma_ads_now(): string {
    return function_exists('current_time') ? (string)current_time('Y-m-d H:i:s') : date('Y-m-d H:i:s');
}

/**
 * Laeuft die Anzeige gerade? Freigegeben (veroeffentlicht + "Schaltung aktiv",
 * beides setzt nur die Redaktion) und innerhalb von Start/Ende. Die
 * Platz-Schalter der Werbeverwaltung prueft ma_active_ads() getrennt.
 */
function ma_ad_is_running(WP_Post $ad, string $now = ''): bool {
    if ($ad->post_type !== 'ma_ad' || $ad->post_status !== 'publish') return false;
    if ((string)get_post_meta($ad->ID, 'ma_ad_active', true) !== '1') return false;
    $now = $now !== '' ? $now : ma_ads_now();
    $start = (string)get_post_meta($ad->ID, 'ma_ad_start', true);
    $end = (string)get_post_meta($ad->ID, 'ma_ad_end', true);
    if ($start !== '' && $start > $now) return false;
    if ($end !== '' && $end < $now) return false;
    return true;
}

/**
 * Alle laufenden, freigegebenen Anzeigen eines Platzes.
 *
 * Reihenfolge: hoechste Prioritaet zuerst, dann die neueste. Die Prioritaet
 * wird in PHP sortiert und nicht per meta_key abgefragt: meta_key setzt in
 * WP_Query voraus, dass der Schluessel existiert, und blendete importierte
 * Anzeigen ohne ma_ad_priority aus (Befund aus 1.4).
 *
 * @return WP_Post[]
 */
function ma_active_ads(string $slot, int $limit = 0): array {
    if (!in_array($slot, ma_ad_slots(), true)) return [];
    if (!get_option('ma_ads_enabled', 0)) return [];
    $enabled = (array)get_option('ma_ad_slots', []);
    if (empty($enabled[$slot])) return [];

    // Pro Seitenaufruf nur eine Abfrage je Platz. ma_active_ads_reset() leert
    // den Stand (Tests, Vorschau nach dem Speichern).
    $cache =& $GLOBALS['ma_active_ads_cache'];
    if (!is_array($cache)) $cache = [];
    if (!isset($cache[$slot])) {
        $now = ma_ads_now();
        $q = new WP_Query([
            'post_type' => 'ma_ad',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'no_found_rows' => true,
            'orderby' => ['date' => 'DESC'],
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'ma_ad_slot', 'value' => $slot],
                ['key' => 'ma_ad_active', 'value' => '1'],
                ['relation' => 'OR', ['key' => 'ma_ad_start', 'compare' => 'NOT EXISTS'], ['key' => 'ma_ad_start', 'value' => '', 'compare' => '='], ['key' => 'ma_ad_start', 'value' => $now, 'compare' => '<=', 'type' => 'DATETIME']],
                ['relation' => 'OR', ['key' => 'ma_ad_end', 'compare' => 'NOT EXISTS'], ['key' => 'ma_ad_end', 'value' => '', 'compare' => '='], ['key' => 'ma_ad_end', 'value' => $now, 'compare' => '>=', 'type' => 'DATETIME']],
            ],
        ]);
        $ads = [];
        foreach ((array)$q->posts as $ad) {
            if (!$ad instanceof WP_Post) continue;
            if ((string)get_post_meta($ad->ID, 'ma_ad_slot', true) !== $slot) continue;
            if (!ma_ad_is_running($ad, $now)) continue;
            $ads[] = $ad;
        }
        usort($ads, static function (WP_Post $a, WP_Post $b): int {
            $pa = (float)get_post_meta($a->ID, 'ma_ad_priority', true);
            $pb = (float)get_post_meta($b->ID, 'ma_ad_priority', true);
            if ($pa !== $pb) return $pa < $pb ? 1 : -1;
            return strcmp((string)$b->post_date, (string)$a->post_date);
        });
        $cache[$slot] = $ads;
    }
    return $limit > 0 ? array_slice($cache[$slot], 0, $limit) : $cache[$slot];
}

function ma_active_ads_reset(): void {
    $GLOBALS['ma_active_ads_cache'] = [];
}

/** Rueckwaertskompatibel: die erste laufende Anzeige des Platzes. */
function ma_active_ad(string $slot): ?WP_Post {
    $ads = ma_active_ads($slot, 1);
    return $ads[0] ?? null;
}

/**
 * Startpunkt der Rotation drehen. Deterministisch pro Aufruf (Platz + Minute),
 * damit derselbe Seitenaufbau stabil bleibt und ein Seiten-Cache nicht immer
 * dieselbe Anzeige vorne zeigt; im Browser wechselt ad-rotation.js weiter.
 */
function ma_ads_rotate(array $ads, string $slot, ?int $seed = null): array {
    $n = count($ads);
    if ($n < 2) return array_values($ads);
    $seed = $seed ?? (int)floor(time() / 60);
    $offset = (int)(sprintf('%u', crc32($slot.'|'.$seed)) % $n);
    return array_merge(array_slice($ads, $offset), array_slice($ads, 0, $offset));
}

function ma_render_ad_item(WP_Post $ad, bool $hidden = false): string {
    $url = esc_url((string)get_post_meta($ad->ID, 'ma_ad_url', true));
    $sponsor = esc_html((string)get_post_meta($ad->ID, 'ma_ad_sponsor', true));
    $img = get_the_post_thumbnail_url($ad, 'large');

    // Vorgabe §2: Liegt ein Bannerbild im Backend, wird es ausgeliefert; ohne
    // Bild erscheint das Textmotiv - nie ein leerer Platz und nie ein
    // 'Platz frei'-Hinweis auf der Live-Seite.
    $body = $img
        ? '<img src="'.esc_url($img).'" alt="'.esc_attr(get_the_title($ad)).'" loading="lazy" decoding="async">'
        : '<span class="ma-ad__text">'.esc_html(get_the_title($ad)).'</span>';
    if ($url) $body = '<a href="'.$url.'" rel="sponsored noopener" target="_blank">'.$body.'</a>';
    return '<div class="ma-ad__item" data-ma-ad-id="'.esc_attr((string)$ad->ID).'"'.($hidden ? ' hidden' : '').'>'.$body.($sponsor ? '<p>'.$sponsor.'</p>' : '').'</div>';
}

/**
 * Ein Platz mit allen laufenden Anzeigen. Die erste ist sichtbar, die uebrigen
 * warten (hidden) und werden von ad-rotation.js nacheinander eingeblendet.
 * Ohne laufende Anzeige: leere Ausgabe, kein reservierter Platz.
 */
function ma_render_ads(string $slot, int $limit = 0, int $interval_ms = 8000): string {
    $ads = ma_ads_rotate(ma_active_ads($slot, $limit), $slot);
    if (!$ads) return '';
    $items = '';
    foreach ($ads as $i => $ad) $items .= ma_render_ad_item($ad, $i > 0);
    $count = count($ads);
    // Mit vollen Angaben: registriert das Skript auch dann, wenn der Platz vor
    // wp_enqueue_scripts ausgegeben wird. Es landet im Footer.
    if ($count > 1 && function_exists('wp_enqueue_script')) wp_enqueue_script('ma-ad-rotation', MA_CORE_URL.'assets/ad-rotation.js', [], MA_CORE_VERSION, true);
    return '<aside class="ma-ad ma-ad--'.esc_attr($slot).($count > 1 ? ' ma-ad--rotation' : '').'" aria-label="Anzeige"'
        .' data-ma-ad-slot="'.esc_attr($slot).'" data-ma-ad-count="'.esc_attr((string)$count).'"'
        .($count > 1 ? ' data-ma-ad-rotate="'.esc_attr((string)max(3000, $interval_ms)).'"' : '')
        .'><small class="ma-ad__label">Anzeige</small>'.$items.'</aside>';
}

/** Bisheriger Einstieg fuer Theme und Shortcode: jetzt mit Rotation. */
function ma_render_ad(string $slot): string {
    return ma_render_ads($slot);
}

function ma_ad_shortcode($atts): string {
    $a = shortcode_atts(['slot' => ''], $atts);
    return in_array($a['slot'], ma_ad_slots(), true) ? ma_render_ads($a['slot']) : '';
}
