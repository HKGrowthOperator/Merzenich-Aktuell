<?php
/**
 * Eingang: jede Formular-Einsendung der WordPress-Seite liegt im Backend
 * (30.09.2026). Vorher ging sie nur per Mail raus; ohne eingerichteten
 * Mailversand wäre sie verloren. Merzenich Aktuell → Eingang.
 *
 * Nicht öffentlich, keine Suche, keine REST-Ausgabe. Anhänge liegen in der
 * Mediathek, am Eingang angehängt. Lesen und bearbeiten nur Redaktion und
 * Administration (edit_others_posts), nie Partner.
 */
if (!defined('ABSPATH')) { exit; }

add_action('init', function (): void {
    register_post_type('ma_eingang', [
        'labels' => ['name' => 'Eingang', 'singular_name' => 'Einsendung', 'menu_name' => 'Eingang', 'all_items' => 'Eingang', 'edit_item' => 'Einsendung', 'search_items' => 'Eingang durchsuchen', 'not_found' => 'Keine Einsendungen.'],
        'public' => false, 'show_ui' => true, 'show_in_menu' => 'merzenich-aktuell', 'show_in_rest' => false, 'exclude_from_search' => true,
        'supports' => ['title', 'editor'], 'capability_type' => 'post',
        'capabilities' => ['create_posts' => 'do_not_allow', 'edit_posts' => 'edit_others_posts', 'edit_others_posts' => 'edit_others_posts', 'delete_posts' => 'edit_others_posts', 'read_private_posts' => 'edit_others_posts', 'publish_posts' => 'edit_others_posts'],
        'map_meta_cap' => true,
    ]);
    foreach (['neu' => 'Neu', 'erledigt' => 'Erledigt'] as $status => $label) {
        register_post_status('ma_' . $status, ['label' => $label, 'public' => false, 'internal' => false, 'show_in_admin_all_list' => true, 'show_in_admin_status_list' => true,
            'label_count' => _n_noop($label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>')]);
    }
});

const MA_EINGANG_TYPEN = ['kontakt' => 'Kontakt', 'meldung' => 'Meldung', 'termin' => 'Termin', 'verein' => 'Verein', 'werbung' => 'Werbung', 'immobilie' => 'Immobilie', 'trauer' => 'Traueranzeige', 'familie' => 'Familienanzeige', 'partner' => 'Partner-Antrag'];

/** Legt eine Einsendung ab. $dateien: Pfade aus wp_handle_upload. Gibt die ID zurück (0 bei Fehler). */
function ma_eingang_speichern(array $f, array $dateien = []): int {
    $typ = sanitize_key($f['typ'] ?? 'kontakt');
    $titel = (MA_EINGANG_TYPEN[$typ] ?? ucfirst($typ)) . ': ' . sanitize_text_field($f['betreff'] ?? '') . ' (' . sanitize_text_field($f['name'] ?? '') . ')';
    $id = wp_insert_post(['post_type' => 'ma_eingang', 'post_status' => 'ma_neu', 'post_title' => wp_strip_all_tags($titel), 'post_content' => sanitize_textarea_field($f['text'] ?? '')], true);
    if (is_wp_error($id) || !$id) return 0;
    foreach (['typ', 'art', 'name', 'email', 'telefon', 'betreff', 'bildrechte'] as $k) if (isset($f[$k]) && $f[$k] !== '') update_post_meta($id, '_ma_eingang_' . $k, sanitize_text_field((string) $f[$k]));
    if ($dateien) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        foreach ($dateien as $pfad) {
            $typ_datei = wp_check_filetype(basename($pfad));
            $anhang = wp_insert_attachment(['post_mime_type' => $typ_datei['type'], 'post_title' => 'Einsendung ' . $id . ': ' . basename($pfad), 'post_status' => 'inherit'], $pfad, $id);
            if ($anhang && !is_wp_error($anhang)) {
                wp_update_attachment_metadata($anhang, wp_generate_attachment_metadata($anhang, $pfad));
                // Bildrechte des Einsenders mitschreiben; „geprüft“ setzt nur die Redaktion.
                update_post_meta($anhang, '_ma_eingang_bildrechte', !empty($f['bildrechte']) ? '1' : '');
            }
        }
    }
    return (int) $id;
}

/* ---------------------------------------------------------- Liste und Ansicht */
add_filter('manage_ma_eingang_posts_columns', fn($c) => ['cb' => $c['cb'] ?? '', 'title' => 'Einsendung', 'ma_typ' => 'Art', 'ma_kontakt' => 'Kontakt', 'ma_anhang' => 'Anhang', 'date' => 'Eingang']);
add_action('manage_ma_eingang_posts_custom_column', function (string $spalte, int $id): void {
    $m = fn($k) => (string) get_post_meta($id, '_ma_eingang_' . $k, true);
    if ($spalte === 'ma_typ') echo esc_html((MA_EINGANG_TYPEN[$m('typ')] ?? $m('typ')) . ($m('art') !== '' ? ' · ' . $m('art') : ''));
    if ($spalte === 'ma_kontakt') echo esc_html($m('email')) . ($m('telefon') !== '' ? '<br>' . esc_html($m('telefon')) : '');
    if ($spalte === 'ma_anhang') {
        $n = count(get_attached_media('', $id));
        echo $n ? esc_html($n . ' Datei' . ($n > 1 ? 'en' : '')) . '<br><span class="description">Bildrechte ' . ($m('bildrechte') === '1' ? 'bestätigt' : 'fehlen') . '</span>' : '–';
    }
}, 10, 2);

add_action('add_meta_boxes_ma_eingang', function (WP_Post $p): void {
    add_meta_box('ma-eingang', 'Absender und Anhänge', function (WP_Post $p): void {
        $m = fn($k) => (string) get_post_meta($p->ID, '_ma_eingang_' . $k, true);
        echo '<p><strong>' . esc_html(MA_EINGANG_TYPEN[$m('typ')] ?? $m('typ')) . '</strong>' . ($m('art') !== '' ? ' · ' . esc_html($m('art')) : '') . '</p>';
        echo '<p>' . esc_html($m('name')) . '<br><a href="mailto:' . esc_attr($m('email')) . '">' . esc_html($m('email')) . '</a>' . ($m('telefon') !== '' ? '<br><a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $m('telefon'))) . '">' . esc_html($m('telefon')) . '</a>' : '') . '</p>';
        foreach (get_attached_media('', $p->ID) as $a) {
            $url = wp_get_attachment_url($a->ID);
            echo '<p>' . (wp_attachment_is_image($a->ID) ? wp_get_attachment_image($a->ID, 'medium', false, ['style' => 'max-width:100%;height:auto']) . '<br>' : '') . '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html(basename($url)) . '</a></p>';
        }
        echo '<p class="description">Bildrechte des Einsenders: ' . ($m('bildrechte') === '1' ? 'bestätigt' : 'nicht bestätigt') . '. Status „Erledigt“ setzen, wenn die Einsendung bearbeitet ist.</p>';
    }, 'ma_eingang', 'side', 'high');
});

add_action('post_submitbox_misc_actions', function (WP_Post $p): void {
    if ($p->post_type !== 'ma_eingang') return;
    echo '<div class="misc-pub-section"><label><input type="checkbox" name="ma_eingang_erledigt" value="1"' . checked($p->post_status, 'ma_erledigt', false) . '> Erledigt</label></div>';
});
add_filter('wp_insert_post_data', function (array $d): array {
    // Speichern im Editor: der Haken „Erledigt“ bestimmt den Status.
    if (($d['post_type'] ?? '') === 'ma_eingang' && ($_POST['action'] ?? '') === 'editpost') $d['post_status'] = !empty($_POST['ma_eingang_erledigt']) ? 'ma_erledigt' : 'ma_neu';
    return $d;
});

add_action('admin_menu', function (): void {
    $n = (int) (wp_count_posts('ma_eingang')->ma_neu ?? 0);
    global $submenu;
    if (!$n || empty($submenu['merzenich-aktuell'])) return;
    foreach ($submenu['merzenich-aktuell'] as $i => $eintrag) if (($eintrag[2] ?? '') === 'edit.php?post_type=ma_eingang') $submenu['merzenich-aktuell'][$i][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $n . '</span></span>';
}, 99);
