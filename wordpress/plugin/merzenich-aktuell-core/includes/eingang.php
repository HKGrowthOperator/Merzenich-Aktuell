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

const MA_EINGANG_TYPEN = ['kontakt' => 'Kontakt', 'meldung' => 'Meldung', 'termin' => 'Termin', 'verein' => 'Verein', 'werbung' => 'Werbung', 'immobilie' => 'Immobilie', 'stelle' => 'Stellenanzeige', 'trauer' => 'Traueranzeige', 'familie' => 'Familienanzeige', 'partner' => 'Partner-Antrag', 'korrektur' => 'Korrektur', 'foto' => 'Foto des Tages'];

/** Legt eine Einsendung ab. $dateien: Pfade aus wp_handle_upload. Gibt die ID zurück (0 bei Fehler). */
function ma_eingang_speichern(array $f, array $dateien = []): int {
    $typ = sanitize_key($f['typ'] ?? 'kontakt');
    $titel = (MA_EINGANG_TYPEN[$typ] ?? ucfirst($typ)) . ': ' . sanitize_text_field($f['betreff'] ?? '') . ' (' . sanitize_text_field($f['name'] ?? '') . ')';
    $id = wp_insert_post(['post_type' => 'ma_eingang', 'post_status' => 'ma_neu', 'post_title' => wp_strip_all_tags($titel), 'post_content' => sanitize_textarea_field($f['text'] ?? '')], true);
    if (is_wp_error($id) || !$id) return 0;
    foreach (['typ', 'art', 'name', 'email', 'telefon', 'betreff', 'bildrechte', 'fotograf', 'zustimmung'] as $k) if (isset($f[$k]) && $f[$k] !== '') update_post_meta($id, '_ma_eingang_' . $k, sanitize_text_field((string) $f[$k]));
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

/* ---------------------------------------------------------- Nach Art filtern (03.10.2026) */
add_action('restrict_manage_posts', function (string $typ): void {
    if ($typ !== 'ma_eingang') return;
    $jetzt = sanitize_key(wp_unslash($_GET['ma_eingang_typ'] ?? ''));
    echo '<select name="ma_eingang_typ" aria-label="Nach Art filtern"><option value="">Alle Arten</option>';
    foreach (MA_EINGANG_TYPEN as $k => $l) printf('<option value="%s"%s>%s</option>', esc_attr($k), selected($jetzt, $k, false), esc_html($l));
    echo '</select>';
});
add_action('pre_get_posts', function (WP_Query $q): void {
    if (!is_admin() || !$q->is_main_query() || $q->get('post_type') !== 'ma_eingang') return;
    $t = sanitize_key(wp_unslash($_GET['ma_eingang_typ'] ?? ''));
    if ($t !== '' && isset(MA_EINGANG_TYPEN[$t])) $q->set('meta_query', [['key' => '_ma_eingang_typ', 'value' => $t]]);
});

/* ---------------------------------------------------------- Als Entwurf übernehmen (03.10.2026) */

/** Zielart einer Einsendung: Meldung, Termin, Immobilie, Stelle, Trauer-, Familienanzeige, Werbemittel, Tipp, Unternehmen. */
function ma_eingang_ziel(string $typ, string $art = ''): string {
    if ($typ === 'werbung') return in_array($art, ['tipp', 'sponsoring'], true) ? 'ma_tip' : ($art === 'unternehmen' ? 'ma_business' : 'ma_ad');
    return ['meldung' => 'post', 'verein' => 'post', 'termin' => 'ma_event', 'immobilie' => 'ma_property', 'stelle' => 'ma_job', 'trauer' => 'ma_obituary', 'familie' => 'ma_family_notice'][$typ] ?? '';
}

/**
 * Legt aus einer Einsendung einen Entwurf der passenden Inhaltsart an (nie
 * veröffentlicht). Absender, E-Mail und Telefon bleiben als interne Felder
 * (_ma_einsender_*) am Entwurf, nie öffentlich. Ein Bild wird nur dann
 * Beitragsbild, wenn der Einsender die Bildrechte bestätigt hat; die
 * redaktionelle Rechteprüfung bleibt offen. Die Einsendung wird „Erledigt“.
 * Ein zweiter Aufruf liefert den schon angelegten Entwurf.
 */
function ma_eingang_uebernehmen(int $id): int {
    $e = get_post($id);
    if (!$e || $e->post_type !== 'ma_eingang') return 0;
    $m = fn(string $k): string => (string) get_post_meta($id, '_ma_eingang_' . $k, true);
    $ziel = ma_eingang_ziel($m('typ'), $m('art'));
    if ($ziel === '') return 0;
    $vorhanden = (int) get_post_meta($id, '_ma_eingang_entwurf', true);
    if ($vorhanden && get_post($vorhanden)) return $vorhanden;
    $neu = wp_insert_post(['post_type' => $ziel, 'post_status' => 'draft', 'post_title' => $m('betreff') !== '' ? $m('betreff') : $e->post_title, 'post_content' => $e->post_content, 'post_author' => get_current_user_id()], true);
    if (is_wp_error($neu) || !$neu) return 0;
    foreach (['name', 'email', 'telefon', 'art'] as $k) if ($m($k) !== '') update_post_meta($neu, '_ma_einsender_' . $k, $m($k));
    update_post_meta($neu, '_ma_aus_eingang', $id);
    if ($ziel === 'ma_family_notice' && $m('art') !== '' && taxonomy_exists('ma_family_type') && term_exists($m('art'), 'ma_family_type')) wp_set_object_terms($neu, $m('art'), 'ma_family_type');
    if ($m('bildrechte') === '1') foreach (get_attached_media('image', $id) as $a) { set_post_thumbnail($neu, $a->ID); break; }
    update_post_meta($id, '_ma_eingang_entwurf', (int) $neu);
    wp_update_post(['ID' => $id, 'post_status' => 'ma_erledigt']);
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen((int) $neu, 'Aus dem Eingang übernommen', $e->post_title);
    return (int) $neu;
}

function ma_eingang_uebernehmen_link(int $id): string {
    return wp_nonce_url(admin_url('admin-post.php?action=ma_eingang_uebernehmen&eingang=' . $id), 'ma_eingang_uebernehmen_' . $id);
}

add_action('admin_post_ma_eingang_uebernehmen', function (): void {
    $id = (int) ($_GET['eingang'] ?? 0);
    check_admin_referer('ma_eingang_uebernehmen_' . $id);
    if (!$id || !current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) wp_die('Keine Berechtigung.', 403);
    $neu = ma_eingang_uebernehmen($id);
    if (!$neu) wp_die('Für diese Art von Einsendung gibt es keinen passenden Inhaltstyp (z. B. Kontakt, Korrektur, Partner-Antrag). Bitte direkt beantworten.', 400);
    wp_safe_redirect(admin_url('post.php?post=' . $neu . '&action=edit')); exit;
});

add_filter('post_row_actions', function (array $a, WP_Post $p): array {
    if ($p->post_type !== 'ma_eingang' || !current_user_can('edit_others_posts')) return $a;
    $ziel = ma_eingang_ziel((string) get_post_meta($p->ID, '_ma_eingang_typ', true), (string) get_post_meta($p->ID, '_ma_eingang_art', true));
    if ($ziel === '') return $a;
    $entwurf = (int) get_post_meta($p->ID, '_ma_eingang_entwurf', true);
    $name = get_post_type_object($ziel)->labels->singular_name ?? $ziel;
    $a['ma_uebernehmen'] = $entwurf && get_post($entwurf) ? '<a href="' . esc_url(get_edit_post_link($entwurf)) . '">Entwurf öffnen</a>' : '<a href="' . esc_url(ma_eingang_uebernehmen_link($p->ID)) . '">Als Entwurf übernehmen (' . esc_html($name) . ')</a>';
    return $a;
}, 10, 2);

add_action('add_meta_boxes_ma_eingang', function (WP_Post $p): void {
    $ziel = ma_eingang_ziel((string) get_post_meta($p->ID, '_ma_eingang_typ', true), (string) get_post_meta($p->ID, '_ma_eingang_art', true));
    if ($ziel === '') return;
    add_meta_box('ma-eingang-uebernehmen', 'Weiterbearbeiten', function (WP_Post $p) use ($ziel): void {
        $entwurf = (int) get_post_meta($p->ID, '_ma_eingang_entwurf', true);
        $name = get_post_type_object($ziel)->labels->singular_name ?? $ziel;
        if ($entwurf && get_post($entwurf)) { echo '<p><a class="button" href="' . esc_url(get_edit_post_link($entwurf)) . '">Entwurf öffnen</a></p>'; return; }
        echo '<p><a class="button button-primary" href="' . esc_url(ma_eingang_uebernehmen_link($p->ID)) . '">Als Entwurf übernehmen</a></p><p class="description">Legt eine(n) ' . esc_html($name) . ' als Entwurf an. Veröffentlicht wird erst nach Prüfung und Freigabe. Kontaktdaten bleiben intern.</p>';
    }, 'ma_eingang', 'side', 'high');
});
