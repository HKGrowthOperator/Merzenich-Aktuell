<?php
/**
 * Schlagwörter aufräumen (Plugin 1.26.0): Doppelungen zusammenführen, damit
 * Google weniger dünne Themenseiten sieht. Einmalig beim ersten Aufruf des
 * Backends; die alte Adresse /thema/<alt>/ leitet dauerhaft (301) weiter.
 *
 * - Ressort als Schlagwort (Blaulicht, Sport, Rathaus): das Schlagwort entfällt,
 *   die Ressortseite ersetzt es. Kategorien werden nicht geändert (Adressen).
 * - Ortsteil als Schlagwort: entfällt, /ort/<ort>/ ersetzt es; Meldungen ohne
 *   Ortsangabe bekommen den Ortsteil.
 * - Unterbegriff (Grundschule, Jugendfußball …): geht im Oberbegriff auf.
 *
 * Gelöscht werden nur die Schlagwörter, nie Meldungen; Meldungen werden nicht
 * gespeichert (keine Veröffentlichungssperre, kein Verlauf), nur ihre Begriffe.
 * Dünne Themen (weniger als 3 Meldungen) bleiben bestehen, sind aber nicht
 * mehr in Google (seo.php, ma_seo_thema_indexierbar).
 */
if (!defined('ABSPATH')) { exit; }

const MA_SCHLAGWORT_STAND = '2026-10-07';
/** Name des Schlagworts => [Art, Ziel]; Art: ressort | ort | thema. */
const MA_SCHLAGWORT_ZUSAMMEN = [
    'Blaulicht' => ['ressort', 'blaulicht'], 'Sport' => ['ressort', 'sport'], 'Rathaus' => ['ressort', 'rathaus'],
    'Merzenich' => ['ort', 'merzenich'], 'Golzheim' => ['ort', 'golzheim'], 'Girbelsrath' => ['ort', 'girbelsrath'],
    'Morschenich' => ['ort', 'morschenich'], 'Bürgewald' => ['ort', 'buergewald'],
    'Grundschule' => ['thema', 'Schule'], 'Jugendfußball' => ['thema', 'Fußball'], 'Seniorennachmittag' => ['thema', 'Senioren'],
    'Haushaltssicherung' => ['thema', 'Haushalt'], 'Verkehrssicherheit' => ['thema', 'Verkehr'],
];

/** Ziel eines Schlagworts (pure Funktion): null = bleibt, sonst [Art, Ziel, Pfad]. */
function ma_schlagwort_ziel(string $name, string $zielSlug = ''): ?array {
    $z = MA_SCHLAGWORT_ZUSAMMEN[trim($name)] ?? null;
    if (!$z) return null;
    $pfad = $z[0] === 'ressort' ? '/' . $z[1] . '/' : ($z[0] === 'ort' ? '/ort/' . $z[1] . '/' : '/thema/' . ($zielSlug !== '' ? $zielSlug : strtolower($z[1])) . '/');
    return [$z[0], $z[1], $pfad];
}

/** Slug wie WordPress ihn für deutsche Begriffe bildet (pure Funktion): Bürgewald → buergewald. */
function ma_schlagwort_slug(string $s): string {
    $s = strtr(mb_strtolower(trim($s)), ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']);
    return trim((string) preg_replace('~[^a-z0-9]+~', '-', $s), '-');
}

/**
 * Schlagwort aus dem Abgleich (pure Funktion, Plugin 1.26.2): null = bleibt,
 * '' = entfällt (Ressort oder Ortsteil, die eigene Seiten haben), sonst der
 * Name des Oberbegriffs. Ohne das legte jeder Abgleich die zusammengeführten
 * Schlagwörter wieder an.
 */
function ma_schlagwort_abbilden(string $name, string $slug = ''): ?string {
    foreach (MA_SCHLAGWORT_ZUSAMMEN as $alt => [$art, $ziel]) {
        if (mb_strtolower(trim($name)) === mb_strtolower($alt) || ($slug !== '' && $slug === ma_schlagwort_slug($alt))) return $art === 'thema' ? $ziel : '';
    }
    return null;
}

/** Weiterleitung für eine alte Themenadresse (pure Funktion). */
function ma_schlagwort_weiterleitung(string $pfad, array $karte): string {
    return preg_match('~^/thema/([^/]+)/?~', strtolower($pfad), $m) && isset($karte[$m[1]]) ? (string) $karte[$m[1]] : '';
}

/** Führt die Zusammenlegung aus. @return array{zusammen:int,meldungen:int} */
function ma_schlagwoerter_zusammenfuehren(): array {
    $karte = (array) get_option('ma_schlagwort_weiterleitungen', []);
    $n = ['zusammen' => 0, 'meldungen' => 0];
    foreach (array_keys(MA_SCHLAGWORT_ZUSAMMEN) as $name) {
        $t = get_term_by('name', $name, 'post_tag');
        if (!$t instanceof WP_Term) continue;
        [$art, $ziel] = MA_SCHLAGWORT_ZUSAMMEN[$name];
        $ids = array_map('intval', (array) get_objects_in_term($t->term_id, 'post_tag'));
        $zielSlug = '';
        if ($art === 'thema') {
            $z = get_term_by('name', $ziel, 'post_tag');
            if (!$z instanceof WP_Term) { $neu = wp_insert_term($ziel, 'post_tag'); if (is_wp_error($neu)) continue; $z = get_term((int) $neu['term_id'], 'post_tag'); }
            $zielSlug = $z->slug;
            foreach ($ids as $id) wp_set_object_terms($id, [(int) $z->term_id], 'post_tag', true);
        } elseif ($art === 'ort' && taxonomy_exists('ma_location')) {
            foreach ($ids as $id) if (!wp_get_object_terms($id, 'ma_location', ['fields' => 'ids'])) wp_set_object_terms($id, $ziel, 'ma_location', false);
        }
        $ziel3 = ma_schlagwort_ziel($name, $zielSlug);
        $karte[$t->slug] = $ziel3[2];
        wp_delete_term($t->term_id, 'post_tag');
        $n['zusammen']++; $n['meldungen'] += count($ids);
    }
    update_option('ma_schlagwort_weiterleitungen', $karte, false);
    return $n;
}

add_action('admin_init', function (): void {
    if (get_option('ma_schlagwoerter_stand') === MA_SCHLAGWORT_STAND || !current_user_can('edit_others_posts')) return;
    update_option('ma_schlagwoerter_stand', MA_SCHLAGWORT_STAND, false);
    update_option('ma_schlagwoerter_bericht', ma_schlagwoerter_zusammenfuehren() + ['zeit' => current_time('mysql')], false);
});

/* Alte Themenadressen dauerhaft weiterleiten. */
add_action('template_redirect', function (): void {
    if (!is_404()) return;
    $ziel = ma_schlagwort_weiterleitung((string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? ''), (array) get_option('ma_schlagwort_weiterleitungen', []));
    if ($ziel !== '') { wp_safe_redirect(home_url($ziel), 301); exit; }
}, 5);
