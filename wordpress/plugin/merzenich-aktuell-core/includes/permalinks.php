<?php
/**
 * Adressen wie im statischen Stand (Audit 28.09.2026, WordPress-Paritaet).
 *
 * Der statische Stand fuehrt Meldungen unter /<ressort>/<slug>/, Themen unter
 * /thema/<slug>/, Ortsteile unter /<ort>/ und Termine unter /termine/<slug>/.
 * WordPress trifft das so:
 *
 * - Permalinks /%category%/%postname%/ und Schlagwort-Basis "thema". Beides
 *   setzt die Aktivierung nur, wenn noch nichts eingestellt ist; eine bewusst
 *   gewaehlte Struktur bleibt unangetastet.
 * - /vereine/<slug>/ ist im statischen Stand beides: Vereinsprofil und Meldung
 *   aus dem Ressort Vereine. Gibt es kein Profil mit dem Slug, aber eine
 *   Meldung, zeigt WordPress die Meldung.
 * - Alles andere, was es nur im statischen Stand gibt, leitet per 301 weiter,
 *   und zwar erst auf einer 404: zuerst ueber ma_legacy_url (setzt der Import
 *   fuer jede Meldung und jeden Termin), dann Ressort-, Orts- und
 *   Themenseiten. Eine Weiterleitung auf die eigene Adresse gibt es nicht.
 */
if (!defined('ABSPATH')) { exit; }

const MA_PERMALINK_STRUKTUR = '/%category%/%postname%/';
const MA_TAG_BASIS = 'thema';

/** Aktivierung: Standard nur setzen, wenn WordPress noch "einfach" eingestellt ist. */
function ma_permalinks_standard(): void {
    if ((string)get_option('permalink_structure', '') === '') update_option('permalink_structure', MA_PERMALINK_STRUKTUR);
    if ((string)get_option('tag_base', '') === '') update_option('tag_base', MA_TAG_BASIS);
}

/** Pfad vereinheitlichen: fuehrender und abschliessender Schraegstrich, kleingeschrieben. */
function ma_pfad(string $pfad): string {
    $pfad = strtolower(trim($pfad));
    $pfad = '/' . trim($pfad, '/') . '/';
    return $pfad === '//' ? '/' : $pfad;
}

/**
 * Ziel fuer eine Adresse aus dem statischen Stand, sonst ''.
 * Reine Nachschlage-Funktion, damit sie ohne WordPress testbar ist.
 */
function ma_legacy_ziel(string $pfad): string {
    $pfad = ma_pfad($pfad);
    if ($pfad === '/' || !preg_match('~^/[a-z0-9/_-]+/$~', $pfad)) return '';

    $ids = get_posts([
        'post_type' => ['post', 'ma_event'], 'post_status' => 'publish', 'numberposts' => 1,
        'fields' => 'ids', 'meta_key' => 'ma_legacy_url', 'meta_value' => $pfad,
    ]);
    if ($ids) return (string)get_permalink((int)$ids[0]);

    $term = null;
    if (preg_match('~^/thema/([a-z0-9-]+)/$~', $pfad, $m)) {
        $term = get_term_by('slug', $m[1], 'post_tag');
    } elseif (preg_match('~^/([a-z0-9-]+)/$~', $pfad, $m)) {
        $term = get_term_by('slug', $m[1], 'category') ?: get_term_by('slug', $m[1], 'ma_location');
        // Vereinsprofil unter der kurzen Adresse des statischen Stands (Menü: /sc-1919-merzenich/).
        if (!$term && ($club = get_page_by_path($m[1], OBJECT, 'ma_club'))) return (string)get_permalink($club->ID);
    }
    if (!$term) return '';
    $link = get_term_link($term);
    return is_wp_error($link) ? '' : (string)$link;
}

/** 301 nur, wenn das Ziel eine andere Adresse ist (keine Schleife). */
function ma_legacy_weiterleitung_fuer(string $pfad): string {
    $ziel = ma_legacy_ziel($pfad);
    if ($ziel === '') return '';
    $zielpfad = (string)(parse_url($ziel, PHP_URL_PATH) ?? '');
    return ma_pfad($zielpfad) === ma_pfad($pfad) ? '' : $ziel;
}

add_action('template_redirect', function (): void {
    if (!is_404()) return;
    $pfad = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    $ziel = ma_legacy_weiterleitung_fuer($pfad);
    if ($ziel !== '') { wp_safe_redirect($ziel, 301); exit; }
});

/** /vereine/<slug>/ ohne Vereinsprofil, aber mit Meldung: Meldung zeigen. */
function ma_vereine_anfrage(array $q): array {
    // /vereine/ bleibt die Liste der Vereinsmeldungen (Kategorie), auch seit es
    // Vereinsprofile gibt (01.10.2026); die Profile stehen dort in der rechten Spalte.
    if (($q['post_type'] ?? '') === 'ma_club' && empty($q['ma_club']) && empty($q['name']) && empty($q['p'])) {
        $neu = ['category_name' => 'vereine'];
        if (!empty($q['paged'])) $neu['paged'] = $q['paged'];
        if (!empty($q['feed'])) $neu['feed'] = $q['feed'];
        return $neu;
    }
    $slug = isset($q['ma_club']) ? (string)$q['ma_club'] : '';
    if ($slug === '' || str_contains($slug, '/')) return $q;
    if (get_page_by_path($slug, OBJECT, 'ma_club')) return $q;
    $ids = get_posts(['name' => $slug, 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids']);
    return $ids ? ['name' => $slug] : $q;
}
add_filter('request', 'ma_vereine_anfrage');
